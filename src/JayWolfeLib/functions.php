<?php

namespace JayWolfeLib;

use JayWolfeLib\Hooks\Hooks;

/**
 * Fetch an array from the specified directory.
 *
 * @param string $file
 * @return array
 */
function fetch_array(string $file): array
{
	/**
	 * Filter the file path for the array directory.
	 */
	$file_path = Hook::apply_filters('jwlib_array_path', __DIR__);

	$pathinfo = pathinfo($file);
	if (!isset($pathinfo['extension']) || $pathinfo['extension'] !== 'php') {
		$file .= 'php';
	}

	$dir = trailingslashit( $file_path );
	$arr = [];
	if (is_readable($dir . $file)) {
		$arr = include $dir . $file;
	}

	return $arr;
}


/**
 * Store data in cache for quick retrieval.
 *
 * @param string $key The transient key.
 * @param int $ttl The time to expiration.
 * @param callable $callback The function to call if the transient
 *                           is expired or does not exist.
 * @param array $args The arguments to pass to the callback function.
 * @return mixed $output
 */
function fragment_cache(string $key, int $ttl, callable $callback, array $args = [])
{
	$key = Hook::apply_filters('fragment_cache_prefix', 'fragment_cache_') . $key;
	$output = get_transient($key);

	$clear_fragments = false;

	if (!empty($_GET) && isset($_GET['clear_fragments'])) $clear_fragments = true;

	if ($output === false || $clear_fragments == true) {
		$output = call_user_func($callback, ...$args);

		set_transient($key, $output, $ttl);
	}

	return $output;
}

/**
 * Delete fragment cache.
 *
 * @param string $key
 * @return void
 */
function delete_fragment_cache(string $key)
{
	$key = Hook::apply_filters('fragment_cache_prefix', 'fragment_cache_') . $key;
	delete_transient($key);
}

/**
 * Validate variable as a boolean.
 *
 * @param mixed $var
 * @return bool|null
 */
function validate_bool($var): ?bool
{
	return filter_var($var, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
}

/**
 * Retrieve the container.
 *
 * @return Container
 */
function container(): Container
{
	static $container;

	if (!$container) {
		$container = new Container();
	}

	return $container;
}