<?php

namespace JayWolfeLib;

use JayWolfeLib\Hooks\Hooks;
use JayWolfeLib\Exception\InvalidConfig;

/**
 * Print log in the log directory.
 *
 * @param string $message
 * @return void
 */
function error_log(string $message, string $plugin_file)
{
	$config = container()->get('config')->get($plugin_file);

	if (null === $config->get('paths')['log']) {
		throw new InvalidConfig("Log path not set for " . plugin_basename($plugin_file) . ".");
	}

	$log_path = $config->get('paths')['log'];

	if (!is_dir($log_path)) {
		mkdir($log_path, 0755, true);
	}

	\error_log($message . PHP_EOL, 3, trailingslashit($log_path) . 'log.txt');
}

/**
 * Fetch an array from the specified file.
 *
 * @param string $file
 * @return array
 */
function fetch_array(string $file, string $plugin_file): array
{
	$config = container()->get('config')->get($plugin_file);

	if (null === $config->get('paths')['arrays']) {
		throw new InvalidConfig("Array path not set for " . plugin_basename($plugin_file) . ".");
	}

	$pathinfo = pathinfo($file);
	if (!isset($pathinfo['extension']) || $pathinfo['extension'] !== 'php') {
		$file .= '.php';
	}

	$dir = trailingslashit( $config->get('paths')['arrays'] );

	$arr = [];
	if (is_readable($dir . $file)) {
		$arr = include $dir . $file;
	}

	if (@!is_array($arr)) {
		throw new InvalidConfig("$file did not return an array.");
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
	$key = Hooks::apply_filters('fragment_cache_prefix', 'fragment_cache_') . $key;
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
	$key = Hooks::apply_filters('fragment_cache_prefix', 'fragment_cache_') . $key;
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
function container(bool $bootstrap = true): Container
{
	static $container;

	if (!$container) {
		$container = new Container();
	}

	if ($bootstrap) {
		Container::bootstrap($container);
	}

	return $container;
}

/**
 * Recursively delete a directory and its contents.
 *
 * @param string $dir The directory to delete.
 * @return void
 */
function rrmdir(string $dir): void
{
	if (is_dir($dir)) {
		$objects = scandir($dir);
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != '.' && $object != '..') {
				if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . '/' . $object))
					rrmdir($dir . DIRECTORY_SEPARATOR . $object);
				else
					unlink($dir . DIRECTORY_SEPARATOR . $object);
			}
		}

		rmdir($dir);
	}
}