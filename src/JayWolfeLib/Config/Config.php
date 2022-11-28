<?php

namespace JayWolfeLib\Config;

use JayWolfeLib\Includes\Dependencies;

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

	public function __construct(array $settings, Dependencies $dependencies)
	{
		$this->settings = $settings;
		$this->dependencies = $dependencies;
	}

	/**
	 * Set a config setting.
	 *
	 * @param string $key
	 * @param mixed $val
	 * @return $val
	 */
	public function set(string $key, $val)
	{
		$key = sanitize_key($key);

		$this->settings[$key] = $val;

		return $this->settings[$key];
	}

	/**
	 * Get a config setting.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key)
	{
		$key = sanitize_key($key);

		if (!isset($this->settings[$key])) {
			return $this->set($key, null);
		}

		return $this->settings[$key];
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
	public function delete(string $key)
	{
		$key = sanitize_key($key);

		if (isset($this->settings[$key])) {
			unset($this->settings[$key]);
		}
	}

	/**
	 * Get the settings array.
	 *
	 * @return array
	 */
	public function get_settings(): array
	{
		return $this->settings;
	}

	public function get_dependencies(): Dependencies
	{
		return $this->dependencies;
	}
}