<?php

namespace JayWolfeLib\Component\WordPress\AdminMenu;

class Slug
{
	private $slug;
	
	private function __construct(string $slug)
	{
		$this->slug = $slug;
	}

	public function fromString(string $slug): self
	{
		return new self($slug);
	}

	public function __toString()
	{
		return $this->slug;
	}
}