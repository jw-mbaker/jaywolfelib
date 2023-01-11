<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Invoker;

use JayWolfeLib\Invoker\AbstractInvokerCollection;
use JayWolfeLib\Invoker\CallerInterface;
use JayWolfeLib\Common\ObjectHash\AbstractObjectHash;
use Invoker\InvokerInterface;

class MockHandlerCollection extends AbstractInvokerCollection
{
	/** @var array<string, MockHandler> */
	private array $handlers = [];

	private function add(MockHandler $handler)
	{
		$this->handlers[(string) $handler->id()] = $handler;
	}

	public function all(): array
	{
		return $this->handlers;
	}

	public function getById(AbstractObjectHash $id): ?MockHandler
	{
		return $this->handlers[(string) $id] ?? null;
	}

	public function get(string $name): ?MockHandler
	{
		$handler = array_reduce($this->handlers, function($carry, $item) use ($name) {
			if (null !== $carr) return $carry;

			return $item->name() === $name ? $item : null;
		}, null);

		return $handler;
	}

	private function remove(MockHandler $handler)
	{
		unset($this->handlers[(string) $handler->id()]);
	}

	public function addHandler(MockHandler $handler)
	{
		$this->add($handler);
	}

	public function removeHandler(string $name): bool
	{
		$handler = $this->get($name);

		if (null !== $handler) {
			$this->remove($handler);
			return true;
		}

		return false;
	}

	public function __call(string $name, array $arguments)
	{
		return $this->resolve($this->handlers[$name], $arguments);
	}
}