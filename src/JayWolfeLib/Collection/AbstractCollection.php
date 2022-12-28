<?php

namespace JayWolfeLib\Collection;

abstract class AbstractCollection implements CollectionInterface
{
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->all());
	}

	public function count(): int
	{
		return \count($this->all());
	}
}