<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\PostType;

use JayWolfeLib\Common\Collection\AbstractCollection;

class PostTypeCollection extends AbstractCollection
{
	/**
	 * @var array<string, PostTypeInterface>
	 */
	private array $postTypes = [];

	private function add(PostTypeInterface $postType)
	{
		$this->postTypes[(string) $postType->id()] = $postType;
	}

	public function registerPostType(PostTypeInterface $postType)
	{
		$this->add($postType);
		$thing = \register_post_type($postType->postType(), $postType->args());

		if (is_wp_error( $thing )) {
			throw new \Exception(
				sprintf('Error registering post type %s.', $postType->postType())
			);
		}
	}

	public function unregisterPostType(string $objectType): bool
	{
		$postType = $this->get($objectType);

		if (null !== $postType) {
			$this->remove($postType);
			return true;
		}

		return false;
	}

	/**
	 * Register a taxonomy to the associated post type(s)
	 *
	 * @param string $taxonomy
	 * @param string|array $objectType
	 * @param array $args
	 */
	public function registerTaxonomy(string $taxonomy, $objectType, array $args = [])
	{
		foreach ((array) $objectType as $t) {
			$postType = $this->get($t);
			if (null === $postType) continue;

			$postType->register_taxonomy($taxonomy, $args);
		}
	}

	public function all(): array
	{
		return $this->postTypes;
	}

	public function getById(PostTypeId $id): ?PostTypeInterface
	{
		return $this->postTypes[(string) $id] ?? null;
	}

	/**
	 * Get the post type by name.
	 *
	 * @param string $objectType
	 * @return PostTypeInterface|null
	 */
	public function get(string $objectType): ?PostTypeInterface
	{
		$postType = array_reduce($this->postTypes, function($carry, $item) use ($objectType) {
			if (null !== $carry) return $carry;

			return $item->postType() === $objectType ? $item : null;
		}, null);

		return $postType;
	}

	private function remove(PostTypeInterface $postType)
	{
		$thing = unregister_post_type($postType->postType());

		if (is_wp_error($thing)) {
			throw new \Exception(
				sprintf('Error removing post type %s', $postType->postType())
			);
		}

		unset($this->postTypes[(string) $postType->id()]);
	}
}