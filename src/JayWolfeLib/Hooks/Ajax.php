<?php

namespace JayWolfeLib\Hooks;

use JayWolfeLib\Input;
use DownShift\WordPress\EventEmitterInterface;

use function JayWolfeLib\container;

class Ajax extends Hooks
{
	/**
	 * Attach an ajax action to a new Handler object.
	 *
	 * @param string $hook
	 * @param callable $callback
	 * @return Handler
	 */
	public static function add_ajax(string $hook, callable $callback): Handler
	{
		$handler = new Handler( container()->get('input'), $callback );

		self::add_action("wp_ajax_{$hook}", $handler);

		return $handler;
	}

	/**
	 * Check if ajax action exists.
	 *
	 * @param string $hook
	 * @return bool
	 */
	public static function has_ajax(string $hook): bool
	{
		return self::has_action("wp_ajax_{$hook}");
	}
}