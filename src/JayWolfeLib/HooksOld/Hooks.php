<?php

namespace JayWolfeLib\Hooks;

use DownShift\WordPress\EventEmitter;
use DownShift\WordPress\EventEmitterInterface;

use function JayWolfeLib\container;

class Hooks
{
	public static function add_action(
		string $hook,
		callable $callback,
		int $priority = 10,
		int $accepted_args = 1
	): EventEmitterInterface {
		return self::get_hook_manager()->on($hook, $callback, $priority, $accepted_args);
	}

	public static function add_filter(
		string $hook,
		callable $callback,
		int $priority = 10,
		int $accepted_args = 1
	): EventEmitterInterface {
		return self::get_hook_manager()->filter($hook, $callback, $priority, $accepted_args);
	}

	public static function do_action(string $hook, ...$args): EventEmitterInterface
	{
		return self::get_hook_manager()->emit($hook, ...$args);
	}

	public static function apply_filters(string $hook, ...$args)
	{
		return self::get_hook_manager()->applyFilters($hook, ...$args);
	}

	public static function has_action(string $hook, $callback = false): bool
	{
		return self::get_hook_manager()->hasEventListener($hook, $callback);
	}

	public static function has_filter(string $hook, $callback = false): bool
	{
		return self::get_hook_manager()->hasFilter($hook, $callback);
	}

	public static function get_hook_manager(): EventEmitterInterface
	{
		return container()->get('hooks');
	}
}