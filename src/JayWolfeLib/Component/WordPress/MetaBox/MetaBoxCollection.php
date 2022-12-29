<?php

namespace JayWolfeLib\Component\WordPress\MetaBox;

use JayWolfeLib\Collection\AbstractInvokerCollection;

class MetaBoxCollection extends AbstractInvokerCollection
{
	/**
	 * @var array<string, MetaBoxInterface>
	 */
	private $meta_boxes = [];

	private function add(string $name, MetaBoxInterface $meta_box)
	{
		$this->meta_boxes[$name] = $meta_box;
	}

	public function add_meta_box(MetaBoxInterface $meta_box)
	{
		$this->add($meta_box->id(), $meta_box);
		add_meta_box(
			$meta_box->meta_id(),
			$meta_box->title(),
			[$this, $meta_box->id()],
			$meta_box->get('screen'),
			$meta_box->get('context'),
			$meta_box->get('priority'),
			$meta_box->get('callback_args')
		);
	}

	public function remove_meta_box(string $id, $screen, string $context)
	{
		$meta_box = array_reduce($this->meta_boxes, function($carry, $item) use ($id, $screen, $context) {
			if (null !== $carry) return $carry;

			if (
				$item->meta_id() === $id &&
				$item->get('screen') === $screen &&
				$item->get('context') === $context
			) {
				return $item;
			}

			return null;
		}, null);

		if (null !== $meta_box) {
			$this->remove($meta_box->id());
		}
	}

	public function all(): array
	{
		return $this->meta_boxes;
	}

	public function get(string $name): ?MetaBoxInterface
	{
		return $this->meta_boxes[$name] ?? null;
	}

	public function remove($name)
	{
		foreach ((array) $name as $n) {
			$meta_box = $this->meta_boxes[$n];

			remove_meta_box($meta_box->meta_id(), $meta_box->get('screen'), $meta_box->get('context'));
			unset($this->meta_boxes[$n]);
		}
	}

	public function __call(string $name, array $arguments)
	{
		$this->invoker->call($this->get($name), $arguments);
	}
}