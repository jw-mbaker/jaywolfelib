<?php

namespace JayWolfeLib\Component\Routing;

trait RouteTrait
{
	protected $id = '';
	protected $action = '';
	protected $callable;
	protected $options = [];

	public function id(): string
	{
		return $this->id;
	}

	public function action(): string
	{
		return $this->action;
	}

	public function add(array $options)
	{
		foreach ($options as $key => $option) {
			$this->options[$key] = $option;
		}
	}

	public function get(string $key)
	{
		return $this->options[$key] ?? null;
	}

	public function set(string $key, $option)
	{
		$this->options[$key] = $option;
	}

	public function remove(string $key)
	{
		unset($this->options[$key]);
	}

	public function all(): array
	{
		return $this->options;
	}

	public function has(string $key): bool
	{
		return array_key_exists($key, $this->options);
	}

	public function clear()
	{
		$this->options = [];
	}
}