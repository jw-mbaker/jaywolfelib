<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\AdminMenu;

use JayWolfeLib\Common\ObjectHash\AbstractObjectHash;

class MenuId extends AbstractObjectHash
{
	public static function fromMenuPage(MenuPageInterface $menuPage): self
	{
		return new self($menuPage);
	}
}