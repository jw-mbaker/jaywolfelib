<?php

namespace JayWolfeLib\Config;

use JayWolfeLib\Container;
use JayWolfeLib\Factory\ConfigFactoryInterface;
use JayWolfeLib\Includes\Dependencies;
use JayWolfeLib\Exception\InvalidConfig;

/**
 * @deprecated 3.0.0
 */
class Factory implements ConfigFactoryInterface
{
	/**
	 * The config container.
	 *
	 * @var Container
	 */
	protected $configContainer;

	/**
	 * The main container.
	 *
	 * @var Container
	 */
	protected $mainContainer;

	public function __construct(Container $configContainer, Container $mainContainer)
	{
		$this->configContainer = $configContainer;
		$this->mainContainer = $mainContainer;
	}

	public function set(string $config_file): ConfigInterface
	{
		if (!is_readable($config_file)) {
			throw new InvalidConfig("$config_file not found.");
		}

		$settings = include $config_file;

		if (!is_array($settings)) {
			throw new InvalidConfig('Config file must return an array of settings.');
		}

		if (!isset($settings['plugin_file'])) {
			throw new InvalidConfig('"plugin_file" not set.');
		}

		$key = plugin_basename($settings['plugin_file']);

		if (!isset($this->configContainer[$key])) {
			$this->configContainer[$key] = new Config($settings, new Dependencies($settings['dependencies'] ?? []));
		}

		return $this->configContainer[$key];
	}

	public function get(string $plugin_file): ConfigInterface
	{
		$key = plugin_basename($plugin_file);

		if (!isset($this->configContainer[$key])) {
			$config_file = dirname( $plugin_file ) . '/config.php';
			return $this->set($config_file);
		}

		return $this->configContainer[$key];
	}

	public function get_container(): Container
	{
		return $this->configContainer;
	}
}