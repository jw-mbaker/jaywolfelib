<?php

namespace JayWolfeLib\Component\WordPress\AdminMenu;

class SubMenuPage implements MenuPageInterface
{
	use MenuPageTrait;

	public const DEFAULTS = [
		'page_title' => '',
		'menu_title' => '',
		'capability' => '',
		'position' => ''
	];

	protected $parent_slug = '';

	public function __construct(string $slug, string $parent_slug, $callable, array $settings = [])
	{
		$this->slug = $slug;
		$this->parent_slug = $parent_slug;
		$this->callable = $callable;

		$this->settings = array_merge(self::DEFAULTS, $settings);

		$this->id = 'submenu_page_' . spl_object_hash($this);
	}

	public function parent_slug(): string
	{
		return $this->parent_slug;
	}
}