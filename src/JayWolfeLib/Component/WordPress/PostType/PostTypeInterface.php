<?php

namespace JayWolfeLib\Component\WordPress\PostType;

use JayWolfeLib\Component\ObjectHash\ObjectHashInterface;

interface PostTypeInterface extends ObjectHashInterface
{
	public function post_type(): string;
	public function args(): array;
	public function register_taxonomy(string $taxonomy, array $args = array());
}