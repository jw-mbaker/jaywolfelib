<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\AdminMenu;

use JayWolfeLib\Invoker\HandlerInterface;

interface MenuPageInterface extends HandlerInterface
{
	public const SLUG = 'slug';
	public const PAGE_TITLE = 'page_title';
	public const MENU_TITLE = 'menu_title';
	public const CAPABILITY = 'capability';
	public const POSITION = 'position';

	public function slug(): string;
}