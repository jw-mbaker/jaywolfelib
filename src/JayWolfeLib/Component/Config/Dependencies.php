<?php

namespace JayWolfeLib\Component\Config;

use JayWolfeLib\Component\ParameterInterface;

class Dependencies implements ParameterInterface
{
	/**
	 * The dependencies.
	 *
	 * @var array
	 */
	private $dependencies = [];

	/**
	 * An array of errors.
	 *
	 * @var array
	 */
	private $errors = [];

	public function __construct(array $dependencies)
	{
		$this->dependencies = $dependencies;
	}

	public function all(): array
	{
		return $this->dependencies;
	}

	public function add(array $dependencies)
	{
		foreach ($dependencies as $key => $value) {
			$this->set($key, $value);
		}
	}

	public function set(string $name, $value)
	{
		$this->dependencies[$name] = $value;
	}

	public function get(string $name)
	{
		return $this->dependencies[$name] ?? null;
	}

	public function has(string $name): bool
	{
		return array_key_exists($name, $this->dependencies);
	}

	public function requirements_met(): bool
	{
		$requirements_met = true;

		if (!$this->is_php_version_dependency_met()) {
			$requirements_met = false;
		}

		if (!$this->is_wp_version_dependency_met()) {
			$requirements_met = false;
		}

		if (!$this->are_required_plugins_dependency_met()) {
			$requirements_met = false;
		}

		return $requirements_met;
	}

	public function remove(string $name)
	{
		unset($this->dependencies[$name]);
	}

	public function clear()
	{
		$this->dependencies = [];
	}

	/**
	 * Get the errors.
	 *
	 * @return array
	 */
	public function get_errors(): array
	{
		return $this->errors;
	}

	private function is_php_version_dependency_met(): bool
	{
		if (!isset($this->dependencies['min_php_version'])) {
			return true;
		}

		if ( 1 == version_compare( PHP_VERSION, $this->dependencies['min_php_version'], '>=' ) ) {
			return true;
		}

		$this->add_error_notice(
			"PHP {$this->dependencies['min_php_version']} is required.",
			"You're running version " . PHP_VERSION
		);

		return false;
	}

	private function is_wp_version_dependency_met(): bool
	{
		global $wp_version;

		if (!isset($this->dependencies['min_wp_version'])) {
			return true;
		}

		if ( 1 == version_compare( $wp_version, $this->dependencies['min_wp_version'], '>=' )) {
			return true;
		}

		$this->add_error_notice(
			"WordPress {$this->dependencies['min_wp_version']} is required.",
			"You're running version $wp_version"
		);

		return false;
	}

	private function is_plugin_active(string $slug): bool
	{
		require_once ABSPATH . '/wp-admin/includes/plugin.php';

		if (is_plugin_active($slug)) {
			return true;
		}

		$this->add_error_notice(
			"$slug is a required plugin.",
			"$slug needs to be installed and activated."
		);

		return false;
	}

	private function are_required_plugins_dependency_met(): bool
	{
		$plugin_dependency_met = true;

		if (empty($this->dependencies['plugins'])) {
			return true;
		}

		$installed_plugins = array_filter($this->dependencies['plugins'], function(string $slug) {
			return $this->is_plugin_active($slug);
		});

		if (count($installed_plugins) !== count($this->dependencies['plugins'])) {
			$plugin_dependency_met = false;
		}

		return $plugin_dependency_met;
	}

	private function add_error_notice(string $message, string $info)
	{
		$this->errors[] = (object) [
			'error_message' => $message,
			'info' => $info
		];
	}
}
