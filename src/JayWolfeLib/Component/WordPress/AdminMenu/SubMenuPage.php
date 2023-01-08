<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\AdminMenu;

class SubMenuPage extends AbstractMenuPage
{
	public const MENU_TYPE = 'submenu_page';
	public const PARENT_SLUG = 'parent_slug';

	protected $parent_slug;

	public function __construct(
		string $slug,
		string $parent_slug,
		$callable,
		string $page_title = self::DEFAULTS[self::PAGE_TITLE],
		string $menu_title = self::DEFAULTS[self::MENU_TITLE],
		string $capability = self::DEFAULTS[self::CAPABILITY],
		$position = self::DEFAULTS[self::POSITION],
		array $map = self::DEFAULTS[self::MAP]
	) {
		parent::__construct($slug, $callable, $page_title, $menu_title, $capability, $position, $map);
		$this->parent_slug = $parent_slug;
	}

	public function parent_slug(): string
	{
		return $this->parent_slug;
	}

	public static function create(array $args): self
	{
		$args = array_merge(self::DEFAULTS, $args);

		return new self(
			$args[self::SLUG],
			$args[self::PARENT_SLUG],
			$args[self::CALLABLE],
			$args[self::PAGE_TITLE],
			$args[self::MENU_TITLE],
			$args[self::CAPABILITY],
			$args[self::POSITION],
			$args[self::MAP]
		);
	}
}