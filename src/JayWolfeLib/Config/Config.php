<?php

namespace JayWolfeLib\Config;

class Config implements ConfigInterface
{
	/**
	 * The plugin file.
	 *
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * An associative array of configuation settings.
	 *
	 * @var array
	 */
	protected $config = [];

	/**
	 * Constructor.
	 *
	 * @param string $plugin_file
	 */
	public function __construct(string $plugin_file)
	{
		$this->plugin_file = $this->config['plugin_file'] = $plugin_file;
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

		$this->config[$key] = $val;

		return $this->config[$key];
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

		if (!isset($this->config[$key])) {
			return $this->set($key, null);
		}

		return $this->config[$key];
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

		if (isset($this->config[$key])) {
			unset($this->config[$key]);
		}
	}

	/**
	 * Get the config array.
	 *
	 * @return array
	 */
	public function get_config(): array
	{
		return $this->config;
	}
}