<?php

namespace JayWolfeLib\Models;

use JayWolfeLib\Container;
use JayWolfeLib\Factory\ModelFactoryInterface;
use JayWolfeLib\Exception\InvalidModel;

class Factory implements ModelFactoryInterface
{
	/**
	 * The model container.
	 *
	 * @var Container
	 */
	protected $modelContainer;

	/**
	 * The main container.
	 *
	 * @var Container
	 */
	protected $mainContainer;

	/**
	 * Constructor.
	 *
	 * @param Container $modelContainer
	 * @param Container $mainContainer
	 */
	public function __construct(Container $modelContainer, Container $mainContainer)
	{
		$this->modelContainer = $modelContainer;
		$this->mainContainer = $mainContainer;
	}

	public function get(string $model): ModelInterface
	{
		if (!class_exists($model)) {
			throw new InvalidModel($model . ' not found.');
		}

		$key = sanitize_key($model);

		if (!isset($this->modelContainer[$key])) {
			$class = new \ReflectionClass($model);
			if (!$class->implementsInterface(ModelInterface::class)) {
				$this->modelContainer[$key] = $this->create($model);
			} else {
				$this->modelContainer[$key] = new $model($this->mainContainer->get('wpdb'), $this);
			}
		}

		return $this->modelContainer[$key];
	}

	protected function create(string $class): ModelInterface
	{
		return eval("return (new class() extends $class implements " . ModelInterface::class . " {});");
	}

	/**
	 * Get the model container.
	 *
	 * @return Container
	 */
	public function get_container(): Container
	{
		return $this->modelContainer;
	}
}