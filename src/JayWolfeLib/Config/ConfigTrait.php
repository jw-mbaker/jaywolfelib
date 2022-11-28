<?php

namespace JayWolfeLib\Config;

trait ConfigTrait
{
	/**
	 * The config object.
	 *
	 * @var ConfigInterface
	 */
	protected $config;

	/**
	 * Set the config.
	 *
	 * @param ConfigInterface $config
	 * @return void
	 */
	public function set_config(ConfigInterface $config)
	{
		$this->config = $config;
	}

	/**
	 * Get the config.
	 *
	 * @return ConfigInterface
	 */
	public function get_config(): ConfigInterface
	{
		return $this->config;
	}
}