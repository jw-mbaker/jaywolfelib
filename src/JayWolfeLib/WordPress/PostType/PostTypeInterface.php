<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\PostType;

interface PostTypeInterface
{
	public function postType(): string;
	public function args(): array;
	public function registerTaxonomy(string $taxonomy, array $args = array());
}