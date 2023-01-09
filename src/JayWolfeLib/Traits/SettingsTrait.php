<?php declare(strict_types=1);

namespace JayWolfeLib\Traits;

trait SettingsTrait
{
	protected $settings = [];

	public function add(array $settings)
	{
		foreach ($settings as $key => $setting) {
			$this->settings[$key] = $setting;
		}
	}

	public function get(string $key)
	{
		return $this->settings[$key] ?? null;
	}

	public function set(string $key, $option)
	{
		$this->settings[$key] = $option;
	}

	public function remove(string $key)
	{
		unset($this->settings[$key]);
	}

	public function all(): array
	{
		return $this->settings;
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