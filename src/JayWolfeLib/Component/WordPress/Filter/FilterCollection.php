<?php

namespace JayWolfeLib\Component\WordPress\Filter;

use JayWolfeLib\Collection\AbstractInvokerCollection;
use Symfony\Component\HttpFoundation\Response;

class FilterCollection extends AbstractInvokerCollection
{
	/**
	 * @var array<string, HookInterface>
	 */
	private $hooks = [];

	private function add(string $name, HookInterface $hook)
	{
		$this->hooks[$name] = $hook;
	}

	public function add_filter(HookInterface $hook)
	{
		$this->add($hook->id(), $hook);
		return add_filter($hook->hook(), [$this, $hook->id()], $hook->get('priority'), $hook->get('num_args'));
	}

	public function add_action(HookInterface $hook)
	{
		return $this->add_filter($hook);
	}

	public function remove_filter(string $hook, $callable, int $priority = 10): bool
	{
		$hooks = array_filter($this->hooks, function($obj) use ($hook, $callable, $priority) {
			return
				$obj->hook() === $hook &&
				$obj->get('callable') === $callable &&
				$obj->get('priority') === $priority;
		});

		if (!empty($hooks)) {
			$this->remove(array_keys($hooks));
			return true;
		}

		return false;
	}

	public function remove_action(string $hook, $callable, int $priority = 10): bool
	{
		return $this->remove_filter($hook, $callable, $priority);
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
			$hook = $this->hooks[$n];

			remove_filter($hook->hook(), [$this, $hook->id()]);
			unset($this->hooks[$n]);
		}
	}

	public function __call(string $name, array $arguments)
	{
		$response = $this->resolve($this->get($name), $arguments);

		if ($response instanceof Response) {
			$response->send();
		}

		return $response;
	}
}
