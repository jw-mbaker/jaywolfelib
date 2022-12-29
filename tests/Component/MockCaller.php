<?php

namespace JayWolfeLib\Tests\Component;

use JayWolfeLib\Collection\AbstractInvokerCollection;
use JayWolfeLib\Component\CallerInterface;
use Invoker\InvokerInterface;
use Invoker\Reflection\CallableReflection;
use ReflectionMethod;
use ReflectionFunction;

class MockCaller extends AbstractInvokerCollection
{
	/** @var array<string, MockHandler> */
	private $handlers = [];

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
		//return $this->invoker->call($this->handlers[$name]->callable(), $arguments);
		return $this->resolve($this->handlers[$name], $arguments);
	}
}