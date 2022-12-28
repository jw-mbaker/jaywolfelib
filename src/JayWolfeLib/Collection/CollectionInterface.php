<?php

namespace JayWolfeLib\Collection;

interface CollectionInterface extends \IteratorAggregate, \Countable
{
	public function all(): array;
	public function get(string $name);
	public function remove(string $name);
}