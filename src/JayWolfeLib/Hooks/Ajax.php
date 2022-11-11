<?php

namespace JayWolfeLib\Hooks;

use DownShift\WordPress\EventEmitterInterface;

class Ajax extends Hooks
{
	public static function add_ajax(string $hook, callable $callback): EventEmitterInterface
	{
		return self::add_action("wp_ajax_{$hook}", $callback);
	}

	public static function has_ajax(string $hook): bool
	{
		return self::has_action("wp_ajax_{$hook}");
	}
}