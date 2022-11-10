<?php

namespace JayWolfeLib\Config;

use JayWolfeLib\Container;
use JayWolfeLib\Factory\ConfigFactoryInterface;

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

	public function get(string $plugin_file): ConfigInterface
	{
		$key = plugin_basename($plugin_file);

		if (!isset($this->configContainer[$key])) {
			$this->configContainer[$key] = new Config($plugin_file);
		}

		return $this->configContainer[$key];
	}

	public function get_container(): Container
	{
		return $this->configContainer;
	}
}