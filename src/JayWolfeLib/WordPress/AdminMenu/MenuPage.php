<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\AdminMenu;

class MenuPage extends AbstractMenuPage
{
	public const ICON_URL = 'icon_url';

	public const DEFAULTS = [
		self::ICON_URL => ''
	];

	private string $icon_url;

	public function __construct(
		string $slug,
		$callable,
		string $page_title = parent::DEFAULTS[self::PAGE_TITLE],
		string $menu_title = parent::DEFAULTS[self::MENU_TITLE],
		string $capability = parent::DEFAULTS[self::CAPABILITY],
		string $icon_url = self::DEFAULTS[self::ICON_URL],
		$position = parent::DEFAULTS[self::POSITION],
		array $map = parent::DEFAULTS[self::MAP]
	) {
		parent::__construct($slug, $callable, $page_title, $menu_title, $capability, $position, $map);
		$this->icon_url = $icon_url;
	}

	public function icon_url(): string
	{
		return $this->icon_url;
	}

	public static function create(array $args): self
	{
		$args = array_merge(parent::DEFAULTS, self::DEFAULTS, $args);

		return new self(
			$args[self::SLUG],
			$args[self::CALLABLE],
			$args[self::PAGE_TITLE],
			$args[self::MENU_TITLE],
			$args[self::CAPABILITY],
			$args[self::ICON_URL],
			$args[self::POSITION],
			$args[self::MAP]
		);
	}
}
