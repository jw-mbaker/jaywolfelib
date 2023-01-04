<?php

namespace JayWolfeLib\Component\WordPress\PostType;

use JayWolfeLib\Component\ObjectHash\ObjectHashTrait;

class PostType implements PostTypeInterface
{
	use ObjectHashTrait;

	public const TYPE = 'post_type';

	/** @var string */
	protected $post_type;

	/** @var array */
	protected $args;

	/**
	 * Constructor.
	 *
	 * @param string $post_type
	 * @param array $args
	 */
	public function __construct(string $post_type, array $args = array())
	{
		$this->post_type = $post_type;
		$this->args = $args;

		$this->set_id_from_type(static::TYPE);
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