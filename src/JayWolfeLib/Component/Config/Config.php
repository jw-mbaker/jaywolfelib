<?php

namespace JayWolfeLib\Component\Config;

class Config implements ConfigInterface
{
	/**
	 * An associative array of configuation settings.
	 *
	 * @var array
	 */
	protected $settings = [];

	/**
	 * The Dependencies object.
	 *
	 * @var Dependencies
	 */
	protected $dependencies;

	/**
	 * Constructor.
	 *
	 * @param array $settings
	 * @param Dependencies|null $dependencies
	 */
	public function __construct(array $settings, Dependencies $dependencies = null)
	{
		$this->settings = $settings;
		$this->dependencies ??= new Dependencies([]);
	}

	public function add(array $settings)
	{
		foreach ($settings as $key => $setting) {
			$this->set($key, $setting);
		}
	}

	/**
	 * Set a config setting.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return $val
	 */
	public function set(string $name, $value)
	{
		$key = sanitize_key($name);

		$this->settings[$key] = $value;

		return $this->settings[$key];
	}

	/**
	 * Get a config setting.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get(string $name)
	{
		$key = sanitize_key($name);

		if (!isset($this->settings[$key])) {
			return $this->set($key, null);
		}

		return $this->settings[$key];
	}

	public function all(): array
	{
		return $this->settings;
	}

	public function has(string $name): bool
	{
		return array_key_exists($name, $this->settings);
	}

	/**
	 * Check if plugin requirements are met.
	 *
	 * @return bool
	 */
	public function requirements_met(): bool
	{
		return $this->dependencies->requirements_met();
	}

	/**
	 * Get the dependency errors.
	 *
	 * @return array
	 */
	public function get_errors(): array
	{
		return $this->dependencies->get_errors();
	}

	/**
	 * Delete a config setting.
	 *
	 * @param string $key
	 * @return void
	 */
	public function remove(string $name)
	{
		$key = sanitize_key($name);

		if (isset($this->settings[$key])) {
			unset($this->settings[$key]);
		}
	}

	public function clear()
	{
		$this->settings = [];
	}

	public function add_dependencies(array $dependencies)
	{
		$this->dependencies->add($dependencies);
	}

	public function set_dependency(string $name, $value)
	{
		$this->dependencies->set($name, $value);
	}

	public function remove_dependency(string $name)
	{
		$this->dependencies->remove($name);
	}

	public function clear_dependencies()
	{
		$this->dependencies->clear();
	}

	public function get_dependencies(): Dependencies
	{
		return $this->dependencies;
	}
}