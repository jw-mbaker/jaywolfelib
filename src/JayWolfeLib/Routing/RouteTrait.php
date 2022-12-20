<?php

namespace JayWolfeLib\Routing;

trait RouteTrait
{
	private $settings = [];

	public function add(array $settings)
	{
		foreach ($settings as $key => $setting) {
			$this->set($key, $setting);
		}
	}

	public function all(): array
	{
		return $this->settings;
	}

	public function get(string $key)
	{
		return $this->setting[$key] ?? null;
	}

	public function remove(string $key)
	{
		unset($this->settings[$key]);
	}

	public function set(string $key, $value)
	{
		$this->settings[$key] = $value;
	}

	public function has(string $key): bool
	{
		return array_key_exists($key, $this->settings);
	}

	public function clear()
	{
		$this->settings = [];
	}
}