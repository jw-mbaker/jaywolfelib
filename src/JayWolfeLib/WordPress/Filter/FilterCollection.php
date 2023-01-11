<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\Filter;

use JayWolfeLib\Invoker\AbstractInvokerCollection;
use Symfony\Component\HttpFoundation\Response;

class FilterCollection extends AbstractInvokerCollection
{
	/**
	 * @var array<string, HookInterface>
	 */
	private array $hooks = [];

	private function add(HookInterface $hook)
	{
		$this->hooks[(string) $hook->id()] = $hook;
	}

	public function addFilter(HookInterface $hook)
	{
		$this->add($hook);
		return add_filter(
			$hook->hook(),
			[$this, (string) $hook->id()],
			$hook->priority(),
			$hook->numArgs()
		);
	}

	public function addAction(HookInterface $hook)
	{
		return $this->addFilter($hook);
	}

	public function removeFilter(string $hook, $callable, int $priority = 10): bool
	{
		$hooks = $this->get($hook, $callable, $priority);

		if (!empty($hooks)) {
			$this->remove(...array_values($hooks));
			return true;
		}

		return false;
	}

	public function removeAction(string $hook, $callable, int $priority = 10): bool
	{
		return $this->removeFilter($hook, $callable, $priority);
	}

	public function all(): array
	{
		return $this->hooks;
	}

	public function getById(HookId $id): ?HookInterface
	{
		return $this->hooks[(string) $id] ?? null;
	}

	/**
	 * Get by hook.
	 *
	 * @return array<string, HookInterface>
	 */
	public function get(string $hook, $callable, int $priority = 10): array
	{
		$hooks = array_filter($this->hooks, function($obj) use ($hook, $callable, $priority) {
			return
				$obj->hook() === $hook &&
				$obj->callable() === $callable &&
				$obj->priority() === $priority;
		});

		return $hooks;
	}

	/**
	 * Removes a hook from the collection.
	 *
	 * @param HookInterface ...$hook
	 */
	private function remove(HookInterface ...$hooks)
	{
		foreach ($hooks as $hook) {
			remove_filter($hook->hook(), [$this, (string) $hook->id()]);
			unset($this->hooks[(string) $hook->id()]);
		}
	}

	public function __call(string $name, array $arguments)
	{
		$response = $this->resolve($this->hooks[$name], $arguments);

		if ($response instanceof Response) {
			$response->send();
		}

		return $response;
	}
}
