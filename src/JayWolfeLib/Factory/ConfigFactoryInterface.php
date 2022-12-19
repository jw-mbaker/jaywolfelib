<?php

namespace JayWolfeLib\Factory;

use JayWolfeLib\Config\ConfigInterface;

/**
 * @deprecated 3.0.0
 */
interface ConfigFactoryInterface extends BaseFactoryInterface
{
	/**
	 * Get a config instance from the container.
	 * Instantiates it if it does not exist.
	 *
	 * @param string $plugin_file
	 * @return ConfigInterface
	 */
	public function get(string $plugin_file): ConfigInterface;
}