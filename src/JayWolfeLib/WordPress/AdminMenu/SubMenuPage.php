<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\AdminMenu;

class SubMenuPage extends AbstractMenuPage
{
	public const PARENT_SLUG = 'parent_slug';

	protected string $parentSlug;

	public function __construct(
		string $slug,
		string $parentSlug,
		$callable,
		string $pageTitle = self::DEFAULTS[self::PAGE_TITLE],
		string $menuTitle = self::DEFAULTS[self::MENU_TITLE],
		string $capability = self::DEFAULTS[self::CAPABILITY],
		$position = self::DEFAULTS[self::POSITION],
		array $map = self::DEFAULTS[self::MAP]
	) {
		parent::__construct($slug, $callable, $pageTitle, $menuTitle, $capability, $position, $map);
		$this->parentSlug = $parentSlug;
	}

	public function parentSlug(): string
	{
		return $this->parentSlug;
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
