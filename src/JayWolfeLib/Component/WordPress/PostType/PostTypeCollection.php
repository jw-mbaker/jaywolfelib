<?php

namespace JayWolfeLib\Component\WordPress\PostType;

use JayWolfeLib\Collection\AbstractCollection;

class PostTypeCollection extends AbstractCollection
{
	/**
	 * @var array<string, PostTypeInterface>
	 */
	private $post_types = [];

	public function add(string $name, PostTypeInterface $post_type)
	{
		$this->post_types[$name] = $post_type;
	}

	public function register_post_type(PostTypeInterface $post_type)
	{
		$this->add($post_type->id(), $post_type);
		$thing = \register_post_type($post_type->post_type(), $post_type->args());

		if (is_wp_error( $thing )) {
			throw new \Exception(
				sprintf('Error registering post type %s.', $post_type->post_type())
			);
		}
	}

	/**
	 * Register a taxonomy to the associated post type(s)
	 *
	 * @param string $taxonomy
	 * @param string|array $object_type
	 * @param array $args
	 */
	public function register_taxonomy(string $taxonomy, $object_type, array $args)
	{
		foreach ((array) $object_type as $t) {
			$post_type = $this->post_types[$t];

			$post_type->register_taxonomy($taxonomy, $args);
		}
	}

	public function all(): array
	{
		return $this->post_types;
	}

	public function get(string $name): ?PostTypeInterface
	{
		return $this->post_types[$name] ?? null;
	}

	public function remove($object_type)
	{
		foreach ((array) $object_type as $t) {
			$post_type = $this->post_types[$t];

			unregister_post_type($post_type->post_type());
			unset($this->post_types[$t]);
		}
	}
}