<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\PostType;

use JayWolfeLib\Collection\AbstractCollection;

class PostTypeCollection extends AbstractCollection
{
	/**
	 * @var array<string, PostTypeInterface>
	 */
	private array $post_types = [];

	private function add(PostTypeInterface $post_type)
	{
		$this->post_types[(string) $post_type->id()] = $post_type;
	}

	public function register_post_type(PostTypeInterface $post_type)
	{
		$this->add($post_type);
		$thing = \register_post_type($post_type->post_type(), $post_type->args());

		if (is_wp_error( $thing )) {
			throw new \Exception(
				sprintf('Error registering post type %s.', $post_type->post_type())
			);
		}
	}

	public function unregister_post_type(string $object_type): bool
	{
		$post_type = $this->get($object_type);

		if (null !== $post_type) {
			$this->remove($post_type);
			return true;
		}

		return false;
	}

	/**
	 * Register a taxonomy to the associated post type(s)
	 *
	 * @param string $taxonomy
	 * @param string|array $object_type
	 * @param array $args
	 */
	public function register_taxonomy(string $taxonomy, $object_type, array $args = [])
	{
		foreach ((array) $object_type as $t) {
			$post_type = $this->get($t);
			if (null === $post_type) continue;

			$post_type->register_taxonomy($taxonomy, $args);
		}
	}

	public function all(): array
	{
		return $this->post_types;
	}

	public function get_by_id(PostTypeId $id): ?PostTypeInterface
	{
		return $this->post_types[(string) $id] ?? null;
	}

	/**
	 * Get the post type by name.
	 *
	 * @param string $object_type
	 * @return PostTypeInterface|null
	 */
	public function get(string $object_type): ?PostTypeInterface
	{
		$post_type = array_reduce($this->post_types, function($carry, $item) use ($object_type) {
			if (null !== $carry) return $carry;

			return $item->post_type() === $object_type ? $item : null;
		}, null);

		return $post_type;
	}

	private function remove(PostTypeInterface $post_type)
	{
		$thing = unregister_post_type($post_type->post_type());

		if (is_wp_error($thing)) {
			throw new \Exception(
				sprintf('Error removing post type %s', $post_type->post_type())
			);
		}

		unset($this->post_types[(string) $post_type->id()]);
	}
}