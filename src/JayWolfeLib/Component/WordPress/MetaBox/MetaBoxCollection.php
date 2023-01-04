<?php

namespace JayWolfeLib\Component\WordPress\MetaBox;

use JayWolfeLib\Collection\AbstractInvokerCollection;
use Symfony\Component\HttpFoundation\Response;
use WP_Screen;

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

	/**
	 * Remove a meta box.
	 *
	 * @param string $meta_id
	 * @param string|array|WP_Screen $screen
	 * @param string $context
	 * @return bool
	 */
	public function remove_meta_box(string $meta_id, $screen, string $context): bool
	{
		$meta_box = $this->get_meta_box($meta_id, $screen, $context);

		if (null !== $meta_box) {
			$this->remove($meta_box->id());
			return true;
		}

		return false;
	}

	public function all(): array
	{
		return $this->meta_boxes;
	}

	public function get(string $name): ?MetaBoxInterface
	{
		return $this->meta_boxes[$name] ?? null;
	}

	/**
	 * Get the meta box object filtered by its id, screen, and context.
	 *
	 * @param string $meta_id
	 * @param string|array|WP_Screen $screen
	 * @param string $context
	 * @return MetaBoxInterface|null
	 */
	public function get_meta_box(string $meta_id, $screen, string $context): ?MetaBoxInterface
	{
		$meta_box = array_reduce($this->meta_boxes, function($carry, $item) use ($meta_id, $screen, $context) {
			if (null !== $carry) return $carry;

			if (
				$item->meta_id() === $meta_id &&
				$item->get('screen') === $screen &&
				$item->get('context') === $context
			) {
				return $item;
			}

			return null;
		}, null);

		return $meta_box;
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
		$response = $this->resolve($this->get($name), $arguments);

		if ($response instanceof Response) {
			$response->send();
		}

		return $response;
	}
}