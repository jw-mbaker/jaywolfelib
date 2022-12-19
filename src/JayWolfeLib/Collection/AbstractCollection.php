<?php

namespace JayWolfeLib\Collection;

abstract class AbstractCollection implements \IteratorAggregate, \Countable
{
	public abstract function add(string $name, $value);
	public abstract function all(): array;
	public abstract function get(string $name);
	public abstract function remove($name);

	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->all());
	}

	public function count(): int
	{
		return \count($this->all());
	}
}