<?php

namespace JayWolfeLib\Component\WordPress\Filter;

use JayWolfeLib\Collection\AbstractInvokerCollection;

class FilterCollection extends AbstractInvokerCollection
{
	/**
	 * @var array<string, HookInterface>
	 */
	private $hooks = [];

	public function add(string $name, HookInterface $hook)
	{
		$this->hooks[$name] = $hook;
	}

	public function add_filter(HookInterface $hook): bool
	{
		$this->add($hook->id(), $hook);
		return add_filter($hook->hook(), [$this, $hook->id()]);
	}

	public function add_action(HookInterface $hook): bool
	{
		return $this->add_filter($hook);
	}

	public function all(): array
	{
		return $this->hooks;
	}

	public function get(string $name): ?HookInterface
	{
		return $this->hooks[$name] ?? null;
	}

	/**
	 * Removes a hook or an array of hooks by name from the collection.
	 *
	 * @param string|string[] $name
	 */
	public function remove($name)
	{
		foreach ((array) $name as $n) {
			unset($this->hooks[$n]);
		}
	}

	public function __call(string $name, array $arguments)
	{
		return $this->invoker->call($name, $arguments);
	}
}