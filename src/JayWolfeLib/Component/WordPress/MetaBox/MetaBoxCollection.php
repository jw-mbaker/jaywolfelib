<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\MetaBox;

use JayWolfeLib\Collection\AbstractInvokerCollection;
use Symfony\Component\HttpFoundation\Response;
use WP_Screen;

class MetaBoxCollection extends AbstractInvokerCollection
{
	/**
	 * @var array<string, MetaBoxInterface>
	 */
	private array $meta_boxes = [];

	private function add(MetaBoxInterface $meta_box)
	{
		$this->meta_boxes[(string) $meta_box->id()] = $meta_box;
	}

	public function add_meta_box(MetaBoxInterface $meta_box)
	{
		$this->add($meta_box);
		add_meta_box(
			$meta_box->meta_id(),
			$meta_box->title(),
			[$this, (string) $meta_box->id()],
			$meta_box->screen(),
			$meta_box->context(),
			$meta_box->priority(),
			$meta_box->callback_args()
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
		$meta_box = $this->get($meta_id, $screen, $context);

		if (null !== $meta_box) {
			$this->remove($meta_box);
			return true;
		}

		return false;
	}

	public function all(): array
	{
		return $this->meta_boxes;
	}

	public function get_by_id(MetaBoxId $id): ?MetaBoxInterface
	{
		return $this->meta_boxes[(string) $id] ?? null;
	}

	/**
	 * Get the meta box object filtered by its id, screen, and context.
	 *
	 * @param string $meta_id
	 * @param string|array|WP_Screen $screen
	 * @param string $context
	 * @return MetaBoxInterface|null
	 */
	public function get(string $meta_id, $screen, string $context): ?MetaBoxInterface
	{
		$meta_box = array_reduce($this->meta_boxes, function($carry, $item) use ($meta_id, $screen, $context) {
			if (null !== $carry) return $carry;

			if (
				$item->meta_id() === $meta_id &&
				$item->screen() === $screen &&
				$item->context() === $context
			) {
				return $item;
			}

			return null;
		}, null);

		return $meta_box;
	}

	private function remove(MetaBoxInterface $meta_box)
	{
		remove_meta_box($meta_box->meta_id(), $meta_box->screen(), $meta_box->context());
		unset($this->meta_boxes[(string) $meta_box->id()]);
	}

	public function __call(string $name, array $arguments)
	{
		$response = $this->resolve($this->meta_boxes[$name], $arguments);

		if ($response instanceof Response) {
			$response->send();
		}

		return $response;
	}
}