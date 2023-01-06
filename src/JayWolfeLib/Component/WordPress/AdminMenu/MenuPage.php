<?php

namespace JayWolfeLib\Component\WordPress\AdminMenu;

class MenuPage extends AbstractMenuPage
{
	public const MENU_TYPE = 'menu_page';

	public const DEFAULTS = [
		'page_title' => '',
		'menu_title' => '',
		'capability' => '',
		'icon_url' => '',
		'position' => null
	];

	public static function create(array $args): self
	{
		return new self(
			Slug::fromString($args['slug']),
			$args['callable'],
			$args['settings'] ?? []
		);
	}
}