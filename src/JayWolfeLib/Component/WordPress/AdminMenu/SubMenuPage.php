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

	public function __construct(string $slug, string $parent_slug, $callable, array $settings = [])
	{
		$this->parent_slug = $parent_slug;
		parent::__construct($slug, $callable, $settings);
	}

	public function parent_slug(): string
	{
		return $this->parent_slug;
	}
}