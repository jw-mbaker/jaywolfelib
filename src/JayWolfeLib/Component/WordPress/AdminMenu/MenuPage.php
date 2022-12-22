<?php

namespace JayWolfeLib\Component\WordPress\AdminMenu;

class MenuPage implements MenuPageInterface
{
	use MenuPageTrait;

	public const DEFAULTS = [
		'page_title' => '',
		'menu_title' => '',
		'capability' => '',
		'icon_url' => '',
		'position' => null
	];

	public function __construct(string $slug, $callable, array $settings = [])
	{
		$this->slug = $slug;
		$this->callable = $callable;

		$this->settings = array_merge(self::DEFAULTS, $settings);

		$this->id = 'menu_page_' . spl_object_hash($this);
	}
}