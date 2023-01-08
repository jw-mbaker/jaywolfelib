<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Invoker;

use JayWolfeLib\Collection\AbstractInvokerCollection;
use JayWolfeLib\Invoker\CallerInterface;
use Invoker\InvokerInterface;

class MockCaller extends AbstractInvokerCollection
{
	/** @var array<string, MockHandler> */
	private array $handlers = [];

	public function all(): array
	{
		return $this->handlers;
	}

	public function get(string $name): ?MockHandler
	{
		return $this->handlers[$name] ?? null;
	}

	public function remove($name)
	{
		foreach ((array) $name as $n) {
			unset($this->handlers[$n]);
		}
	}

	public function addHandler(string $name, MockHandler $handler)
	{
		$this->handlers[$name] = $handler;
	}

	public function getHandler(string $name): MockHandler
	{
		return $this->handlers[$name];
	}

	public function __call(string $name, array $arguments)
	{
		return $this->resolve($this->handlers[$name], $arguments);
	}
}