<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\Filter;

use JayWolfeLib\Collection\AbstractInvokerCollection;
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

	public function add_filter(HookInterface $hook)
	{
		$this->add($hook);
		return add_filter(
			$hook->hook(),
			[$this, (string) $hook->id()],
			$hook->priority(),
			$hook->num_args()
		);
	}

	public function add_action(HookInterface $hook)
	{
		return $this->add_filter($hook);
	}

	public function remove_filter(string $hook, $callable, int $priority = 10): bool
	{
		$hooks = $this->get($hook, $callable, $priority);

		if (!empty($hooks)) {
			$this->remove(...array_values($hooks));
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

	public function get_by_id(HookId $id): ?HookInterface
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
