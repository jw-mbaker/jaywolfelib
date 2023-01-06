<?php

namespace JayWolfeLib\Component\WordPress\AdminMenu;

class MenuId
{
	/** @var string */
	private $id;

	private function __construct(MenuPageInterface $menu_page)
	{
		$this->id = spl_object_hash($menu_page);
	}

	public static function fromMenuPage(MenuPageInterface $menu_page): self
	{
		return new self($menu_page);
	}

	public function __toString()
	{
		return $this->id;
	}
}