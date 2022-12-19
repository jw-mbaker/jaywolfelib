<?php

namespace JayWolfeLib\Models;

use JayWolfeLib\Collection\AbstractCollection;

class ModelCollection extends AbstractCollection
{
	/**
	 * @var array<string, ModelInterface>
	 */
	private $models = [];

	public function add(string $class, ModelInterface $model)
	{
		$this->models[$class] = $model;
	}

	public function all(): array
	{
		return $this->models;
	}

	/**
	 * @param string $class
	 * @return ModelInterface|null
	 */
	public function get(string $class): ?ModelInterface
	{
		return $this->models[$class] ?? null;
	}

	/**
	 * Removes a model or an array of models by name from the collection.
	 *
	 * @param string|string[] $class
	 */
	public function remove($class)
	{
		foreach ((array) $class as $c) {
			unset($this->models[$c]);
		}
	}
}