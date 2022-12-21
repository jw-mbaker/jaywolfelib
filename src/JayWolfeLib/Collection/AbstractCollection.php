<?php

namespace JayWolfeLib\Collection;

use Invoker\InvokerInterface;

abstract class AbstractCollection implements \IteratorAggregate, \Countable
{
	/** @var InvokerInterface */
	protected $invoker;

	public abstract function add(string $name, $value);
	public abstract function all(): array;
	public abstract function get(string $name);
	public abstract function remove($name);

	public function __construct(InvokerInterface $invoker)
	{
		$this->invoker = $invoker;
	}

	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->all());
	}

	public function count(): int
	{
		return \count($this->all());
	}

	protected function get_invoker(): InvokerInterface
	{
		return $this->invoker;
	}
}