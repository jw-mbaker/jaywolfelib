<?php

namespace JayWolfeLib\Models;

use JayWolfeLib\Factory\ModelFactoryInterface;
use JayWolfeLib\Exception\InvalidModel;
use DI\Container;

class Factory implements ModelFactoryInterface
{
	/**
	 * The model collection.
	 *
	 * @var ModelCollection
	 */
	protected $models;

	/**
	 * The main container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param ModelCollection $models
	 * @param Container $container
	 */
	public function __construct(ModelCollection $models, Container $container)
	{
		$this->models = $models;
		$this->container = $container;
	}

	public function get(string $model): ModelInterface
	{
		if (!class_exists($model)) {
			throw new InvalidModel($model . ' not found.');
		}

		if (null === $this->models->get($model)) {
			$class = new \ReflectionClass($model);
			if (!$class->implementsInterface(ModelInterface::class)) {
				$this->models->add($model, $this->create($model));
			} else {
				$this->models->add($model, new $model($this->container->get(\WPDB::class), $this));
			}
		}

		return $this->models->get($model);
	}

	protected function create(string $class): ModelInterface
	{
		return eval("return (new class() extends $class implements " . ModelInterface::class . " {});");
	}

	/**
	 * Get the model collection.
	 *
	 * @return ModelCollection
	 */
	public function get_models(): ModelCollection
	{
		return $this->models;
	}
}