<?php

namespace JayWolfeLib;

use JayWolfeLib\Component\Config\ConfigInterface;
use JayWolfeLib\Component\Config\Config;
use JayWolfeLib\Exception\InvalidConfig;

/**
 * Helper function for installing plugins.
 *
 * @param ConfigInterface|string $config
 * @return void
 */
function install($config)
{
	global $wpdb;

	if (is_string($config)) {
		$config = Config::create($config);
	}

	if (!$config instanceof ConfigInterface) {
		throw new \InvalidArgumentException(
			sprintf('$config argument must be a config file or implement %s.', ConfigInterface::class)
		);
	}

	if (null === $config->get('db')) {
		throw new InvalidConfig(
			sprintf('"db" option not set for %s.', plugin_basename($config->get('plugin_file')))
		);
	}

	$db = fetch_array('tables', $config);
	$db_version = $db['version'];

	$charset_collate = $wpdb->get_charset_collate();
	require_once ABSPATH . '/wp-admin/includes/upgrade.php';

	$key = sanitize_key($config->get('db'));

	add_option("{$key}_db_version", '1', '', 'no');

	$installed_version = get_option( "{$key}_db_version" );

	if ($installed_version !== $db_version) {
		create_table($db, $charset_collate);
		update_option("{$key}_db_version", $db_version);
	}
}

/**
 * Check the database for updates.
 *
 * @param ConfigInterface|string $config
 * @return void
 */
function update_db_check($config): void
{
	if (is_string($config)) {
		$config = Config::create($config);
	}

	if (!$config instanceof ConfigInterface) {
		throw new \InvalidArgumentException(
			sprintf('$config argument must be a config file or implement %s.', ConfigInterface::class)
		);
	}

	if (null === $config->get('db')) {
		throw new InvalidConfig(
			sprintf('"db" option not set for %s.', plugin_basename($config->get('plugin_file')))
		);
	}

	$db = fetch_array('tables', $config);

	$key = sanitize_key($config->get('db'));

	$db_version = $db['version'];

	if (get_option( "{$key}_db_version" ) !== $db_version) {
		install($config);
	}
}

function create_table(array $db, string $charset_collate): void
{
	global $wpdb;

	if (!is_array($db['tables'])) return;

	foreach ($db['tables'] as $table => $fields) {
		if (!is_array($fields)) continue;

		$sql = "CREATE TABLE {$wpdb->prefix}$table";
		$fld = [];
		$fld[] = "id bigint(20) NOT NULL AUTO_INCREMENT";
		foreach ($fields as $field => $structure) {
			$fld[] = "$field {$structure['type']}" .
				(isset($structure['length']) ? "({$structure['length']})" : "") .
				(isset($structure['default']) ? " DEFAULT {$structure['default']}" : "");
		}
		$fld[] = "PRIMARY KEY id (id)";
		$table_fields = implode(", \n", $fld);
		$sql .= " ($table_fields) $charset_collate;";

		dbDelta($sql);
	}
}

function fetch_array(string $file, ConfigInterface $config = null): array
{
	$pathinfo = pathinfo($file);

	if (!isset($pathinfo['extension']) || $pathinfo['extension'] !== 'php') {
		$file .= '.php';
	}

	if (null === $config) {
		$dir = trailingslashit( dirname($file) );
	} else {
		if (!isset($config->get('paths')['arrays'])) {
			throw new InvalidConfig(
				sprintf('Array path not set for %s.', plugin_basename($config->get('plugin_file')))
			);
		}

		$dir = trailingslashit( $config->get('paths')['arrays'] );
	}

	$base_file = basename($file);

	$arr = [];
	if (is_readable($dir . $base_file)) {
		$arr = include $dir . $base_file;
	}

	if (@!is_array($arr)) {
		throw new \InvalidArgumentException(
			sprintf('%s did not return an array.', $file)
		);
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
	$key = apply_filters('fragment_cache_prefix', 'fragment_cache_') . $key;
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
	$key = apply_filters('fragment_cache_prefix', 'fragment_cache_') . $key;
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
 * @param Container|null $c
 * 
 * @return Container
 */
function container(Container $c = null): Container
{
	static $container;

	$container ??= $c;

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
