<?php

namespace JayWolfeLib\Component\Config;

use JayWolfeLib\Traits\SettingsTrait;
use JayWolfeLib\Exception\InvalidConfig;

class Config implements ConfigInterface
{
	use SettingsTrait;

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
		$this->dependencies ??= new Dependencies($settings['dependencies'] ?? []);
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

	/**
	 * Factory method for creating a new Config instance.
	 *
	 * @param string $file The config file location.
	 * @throws InvalidConfig
	 * @return self
	 */
	public static function create(string $file, Dependencies $dependencies = null): self
	{
		if (!is_readable($file)) {
			throw new InvalidConfig(
				sprintf('%s not found.', $file)
			);
		}

		$settings = include $file;

		return new static($settings, $dependencies);
	}
}
