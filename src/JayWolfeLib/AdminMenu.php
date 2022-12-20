<?php

namespace JayWolfeLib;

use JayWolfeLib\Handler\HandlerTrait;

class AdminMenu
{
	use HandlerTrait;

	private $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Add a menu page.
	 *
	 * @param string $page_title
	 * @param string $menu_title
	 * @param string $capability
	 * @param string $slug
	 * @param array<string, mixed> $options
	 * @return mixed
	 */
	public function menu_page(
		string $page_title,
		string $menu_title,
		string $capability,
		string $slug,
		array $options = []
	) {
		$options = $this->sanitize_options($options);

		$handler = $this->build_handler($options['handler'], $options['dependencies'] ?? []);

		add_menu_page($page_title, $menu_title, $capability, $slug, $handler, $options['icon_url'], $options['position']);

		return $handler;
	}

	/**
	 * Add a sub menu page.
	 *
	 * @param string $parent_slug
	 * @param string $page_title
	 * @param string $menu_title
	 * @param string $capability
	 * @param string $menu_slug
	 * @param array<string, mixed> $options
	 * @return mixed
	 */
	public function submenu_page(
		string $parent_slug,
		string $page_title,
		string $menu_title,
		string $capability,
		string $menu_slug,
		array $options = []
	) {
		$options = $this->sanitize_options($options);

		$handler = $this->build_handler($options['handler'], $options['dependencies'] ?? []);

		add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $handler, $options['position']);

		return $handler;
	}

	private function sanitize_options(array $options): array
	{
		$options['handler'] ??= '';
		$options['icon_url'] ??= '';
		$options['position'] ??= null;

		return $options;
	}
}