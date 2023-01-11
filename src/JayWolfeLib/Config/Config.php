<?php declare(strict_types=1);

namespace JayWolfeLib\Config;

use JayWolfeLib\Common\Parameter\ParameterBag;
use JayWolfeLib\Exception\InvalidConfigException;

class Config implements ConfigInterface
{
	protected ParameterBag $settings;
	protected Dependencies $dependencies;

	/**
	 * Constructor.
	 *
	 * @param array $settings
	 * @param Dependencies|null $dependencies
	 */
	public function __construct(array $settings, ?Dependencies $dependencies = null)
	{
		$this->settings = new ParameterBag($settings);
		$this->dependencies ??= new Dependencies($settings['dependencies'] ?? []);
	}

	public function set(string $key, $setting)
	{
		$this->settings->set($key, $setting);
	}

	public function get(string $key)
	{
		return $this->settings->get($key);
	}

	public function remove(string $key)
	{
		$this->settings->remove($key);
	}

	public function has(string $key): bool
	{
		return $this->settings->has($key);
	}

	public function all(?string $key = null): array
	{
		return $this->settings->all($key);
	}

	public function clear()
	{
		$this->settings->clear();
	}

	/**
	 * Check if plugin requirements are met.
	 *
	 * @return bool
	 */
	public function requirementsMet(): bool
	{
		return $this->dependencies->requirementsMet();
	}

	/**
	 * Get the dependency errors.
	 *
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->dependencies->getErrors();
	}

	public function addDependencies(array $dependencies)
	{
		$this->dependencies->add($dependencies);
	}

	public function setDependency(string $name, $value)
	{
		$this->dependencies->set($name, $value);
	}

	public function removeDependency(string $name)
	{
		$this->dependencies->remove($name);
	}

	public function clearDependencies()
	{
		$this->dependencies->clear();
	}

	public function getDependencies(): Dependencies
	{
		return $this->dependencies;
	}

	/**
	 * Factory method for creating a new Config instance.
	 *
	 * @param string $file The config file location.
	 * @throws InvalidConfigException
	 * @return self
	 */
	public static function fromFile(string $file, ?Dependencies $dependencies = null): self
	{
		if (!is_readable($file)) {
			throw new InvalidConfigException(
				sprintf('%s not found.', $file)
			);
		}

		$settings = include $file;

		return new static($settings, $dependencies);
	}
}
