<?php declare(strict_types=1);

namespace JayWolfeLib\Config;

trait ConfigTrait
{
	/**
	 * The config object.
	 */
	protected ?ConfigInterface $config = null;

	/**
	 * Set the config.
	 *
	 * @param ConfigInterface $config
	 * @return void
	 */
	public function setConfig(ConfigInterface $config)
	{
		$this->config = $config;
	}

	/**
	 * Get the config.
	 */
	public function getConfig(): ConfigInterface
	{
		return $this->config;
	}
}