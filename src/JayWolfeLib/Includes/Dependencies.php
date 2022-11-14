<?php

namespace JayWolfeLib\Includes;

class Dependencies
{
	/**
	 * The dependencies.
	 *
	 * @var array
	 */
	private $dependencies = [];

	public function __construct(array $dependencies)
	{
		$this->dependencies = $dependencies;
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

	private function is_php_version_dependency_met(): bool
	{
		if (!isset($this->dependencies['min_php_version'])) {
			return true;
		}

		if ( 1 == version_compare( PHP_VERSION, $this->dependencies['min_php_version'], '>=' ) ) {
			return true;
		}

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

		return false;
	}

	private function is_plugin_active(string $slug): bool
	{
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( $plugin_slug );
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
}