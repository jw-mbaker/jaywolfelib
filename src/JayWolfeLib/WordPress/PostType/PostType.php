<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\PostType;

class PostType implements PostTypeInterface
{
	protected PostTypeId $id;
	protected string $postType;
	protected array $args;

	/**
	 * Constructor.
	 *
	 * @param string $postType
	 * @param array $args
	 */
	public function __construct(string $postType, array $args = [])
	{
		$this->postType = $postType;
		$this->args = $args;
	}

	public function id(): PostTypeId
	{
		return $this->id ??= PostTypeId::fromPostType($this);
	}

	public function postType(): string
	{
		return $this->postType;
	}

	public function args(): array
	{
		return $this->args;
	}

	public function registerTaxonomy(string $taxonomy, array $args = array()): self
	{
		$callback = function() use ($taxonomy, $args) {
			\register_taxonomy($taxonomy, $this->postType, $args);
		};

		if (did_action("registered_post_type_{$this->postType}")) {
			$callback();
		} else {
			add_action("registered_post_type_{$this->postType}", $callback);
		}

		return $this;
	}
}