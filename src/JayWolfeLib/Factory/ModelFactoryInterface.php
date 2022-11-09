<?php

namespace JayWolfeLib\Factory;

use JayWolfeLib\Models\ModelInterface;

interface ModelFactoryInterface extends BaseFactoryInterface
{
	/**
	 * Get a model instance from the container.
	 * Instantiates it if it does not exist.
	 *
	 * @param string $class
	 * @return ModelInterface
	 */
	public function get(string $class): ModelInterface;
}