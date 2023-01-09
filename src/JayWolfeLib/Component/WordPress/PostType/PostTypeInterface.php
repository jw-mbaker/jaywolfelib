<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\PostType;

interface PostTypeInterface
{
	public function post_type(): string;
	public function args(): array;
	public function register_taxonomy(string $taxonomy, array $args = array());
}