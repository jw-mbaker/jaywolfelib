<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\AdminMenu;

class MenuPage extends AbstractMenuPage
{
	public const ICON_URL = 'icon_url';

	public const DEFAULTS = [
		self::ICON_URL => ''
	];

	private string $iconUrl;

	public function __construct(
		string $slug,
		$callable,
		string $pageTitle = parent::DEFAULTS[self::PAGE_TITLE],
		string $menuTitle = parent::DEFAULTS[self::MENU_TITLE],
		string $capability = parent::DEFAULTS[self::CAPABILITY],
		string $iconUrl = self::DEFAULTS[self::ICON_URL],
		$position = parent::DEFAULTS[self::POSITION],
		array $map = parent::DEFAULTS[self::MAP]
	) {
		parent::__construct($slug, $callable, $pageTitle, $menuTitle, $capability, $position, $map);
		$this->iconUrl = $iconUrl;
	}

	public function iconUrl(): string
	{
		return $this->iconUrl;
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
