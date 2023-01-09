<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\AdminMenu;

use JayWolfeLib\ObjectHash\AbstractObjectHash;

class MenuId extends AbstractObjectHash
{
	public static function fromMenuPage(MenuPageInterface $menu_page): self
	{
		return new self($menu_page);
	}
}