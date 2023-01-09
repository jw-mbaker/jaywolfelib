<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\PostType;

class PostType implements PostTypeInterface
{
	protected PostTypeId $id;
	protected string $post_type;
	protected array $args;

	/**
	 * Constructor.
	 *
	 * @param string $post_type
	 * @param array $args
	 */
	public function __construct(string $post_type, array $args = [])
	{
		$this->post_type = $post_type;
		$this->args = $args;
	}

	public function id(): PostTypeId
	{
		return $this->id ??= PostTypeId::fromPostType($this);
	}

	public function post_type(): string
	{
		return $this->post_type;
	}

	public function args(): array
	{
		return $this->args;
	}

	public function register_taxonomy(string $taxonomy, array $args = array()): self
	{
		$callback = function() use ($taxonomy, $args) {
			\register_taxonomy($taxonomy, $this->post_type, $args);
		};

		if (did_action("registered_post_type_{$this->post_type}")) {
			$callback();
		} else {
			add_action("registered_post_type_{$this->post_type}", $callback);
		}

		return $this;
	}
}