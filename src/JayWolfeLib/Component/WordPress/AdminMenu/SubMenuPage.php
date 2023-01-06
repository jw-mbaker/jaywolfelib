<?php

namespace JayWolfeLib\Component\WordPress\AdminMenu;

class SubMenuPage extends AbstractMenuPage
{
	public const MENU_TYPE = 'submenu_page';

	public const DEFAULTS = [
		'page_title' => '',
		'menu_title' => '',
		'capability' => '',
		'position' => ''
	];

	protected $parent_slug;

	public function __construct(Slug $slug, Slug $parent_slug, $callable, array $settings = [])
	{
		$this->parent_slug = $parent_slug;
		parent::__construct($slug, $callable, $settings);
	}

	public function parent_slug(): Slug
	{
		return $this->parent_slug;
	}

	public static function create(array $args): self
	{
		return new self(
			Slug::fromString($args['slug']),
			Slug::fromString($args['parent_slug']),
			$args['callable'],
			$args['settings'] ?? []
		);
	}
}