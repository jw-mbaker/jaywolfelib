<?php

namespace JayWolfeLib\Component\Config;

use JayWolfeLib\Collection\AbstractCollection;

class ConfigCollection extends AbstractCollection
{
	/**
	 * @var array<string, ConfigInterface>
	 */
	private $configs = [];

	public function add(string $name, ConfigInterface $config)
	{
		$this->configs[$name] = $config;
	}

	public function all(): array
	{
		return $this->configs;
	}

	/**
	 * @param string $name
	 * @return ConfigInterface|null
	 */
	public function get(string $name): ?ConfigInterface
	{
		return $this->configs[$name] ?? null;
	}

	/**
	 * Removes a config or an array of configs by name from the collection.
	 *
	 * @param string|string[] $name
	 */
	public function remove($name)
	{
		foreach ((array) $name as $n) {
			unset($this->configs[$n]);
		}
	}
}