<?php

namespace JayWolfeLib\Collection;

interface CollectionInterface extends \IteratorAggregate, \Countable
{
	public function all(): array;
}
