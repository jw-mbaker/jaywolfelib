<?php

namespace JayWolfeLib\Hooks;

use function JayWolfeLib\container;

class MenuPage
{
	/**
	 * Add a menu page.
	 *
	 * @param string $page_title
	 * @param string $menu_title
	 * @param string $capability
	 * @param string $menu_slug
	 * @param callable $callback
	 * @param string $icon_url
	 * @param int|float $position
	 * @return Handler
	 */
	public static function add_menu_page(
		string $page_title,
		string $menu_title,
		string $capability,
		string $menu_slug,
		callable $callback,
		string $icon_url = '',
		$position = null
	): Handler {
		if ($callback instanceof Handler) {
			$handler = $callback;
		} else {
			$handler = new Handler( container()->get('request'), $callback );
		}

		add_menu_page(
			$page_title,
			$menu_title,
			$capability,
			$menu_slug,
			$handler,
			$icon_url,
			$position
		);

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
	 * @param Handler|callable $callback
	 * @param int|float $position
	 * @return Handler
	 */
	public static function add_submenu_page(
		string $parent_slug,
		string $page_title,
		string $menu_title,
		string $capability,
		string $menu_slug,
		callable $callback,
		$position = null
	): Handler {
		if ($callback instanceof Handler) {
			$handler = $callback;
		} else {
			$handler = new Handler( container()->get('request'), $callback );
		}

		add_submenu_page(
			$parent_slug,
			$page_title,
			$menu_title,
			$capability,
			$menu_slug,
			$handler,
			$position
		);

		return $handler;
	}
}