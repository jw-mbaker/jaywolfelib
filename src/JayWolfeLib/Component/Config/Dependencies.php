<?php declare(strict_types=1);

namespace JayWolfeLib\Component\Config;

use JayWolfeLib\Parameter\ParameterBag;

class Dependencies
{
	private ParameterBag $dependencies;
	private ParameterBag $errors;

	public function __construct(array $dependencies = [])
	{
		$this->dependencies = new ParameterBag($dependencies);
		$this->errors = new ParameterBag();
	}

	public function all(): array
	{
		return $this->dependencies->all();
	}

	public function add(array $dependencies)
	{
		$this->depependencies->add($dependencies);
	}

	public function set(string $name, $value)
	{
		$this->dependencies->set($name, $value);
	}

	public function get(string $name)
	{
		return $this->dependencies->get($name);
	}

	public function has(string $name): bool
	{
		return $this->dependencies->has($name);
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
		$this->dependencies->remove($name);
	}

	public function clear()
	{
		$this->dependencies->clear();
	}

	/**
	 * Get the errors.
	 */
	public function get_errors(): array
	{
		return $this->errors->all();
	}

	private function is_php_version_dependency_met(): bool
	{
		if (null === $this->dependencies->get('min_php_version')) {
			return true;
		}

		if ( 1 == version_compare( PHP_VERSION, $this->dependencies->get('min_php_version'), '>=' ) ) {
			return true;
		}

		$this->add_error_notice(
			sprintf('PHP %s is required.', $this->dependencies->get('min_php_version')),
			sprintf("You're running version %s", PHP_VERSION)
		);

		return false;
	}

	private function is_wp_version_dependency_met(): bool
	{
		global $wp_version;

		if (null === $this->dependencies->get('min_wp_version')) {
			return true;
		}

		if ( 1 == version_compare( $wp_version, $this->dependencies->get('min_wp_version'), '>=' )) {
			return true;
		}

		$this->add_error_notice(
			sprintf('WordPress %s is required.', $this->dependencies->get('min_wp_version')),
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

		if (empty($this->dependencies->get('plugins'))) {
			return true;
		}

		$installed_plugins = array_filter($this->dependencies->get('plugins'), function(string $slug) {
			return $this->is_plugin_active($slug);
		});

		if (count($installed_plugins) !== count($this->dependencies->get('plugins'))) {
			$plugin_dependency_met = false;
		}

		return $plugin_dependency_met;
	}

	private function add_error_notice(string $message, string $info)
	{
		$key = count($this->errors);
		$this->errors->set((string) $key, (object) [
			'error_message' => $message,
			'info' => $info
		]);
	}
}
