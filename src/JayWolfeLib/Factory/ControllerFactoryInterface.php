<?php

namespace JayWolfeLib\Factory;

use JayWolfeLib\Controllers\ControllerInterface;

interface ControllerFactoryInterface extends BaseFactoryInterface
{
	/**
	 * Get a controller instance from the container.
	 * Instantiates it if it does not exist.
	 *
	 * @param string $controller
	 * @param array $dependencies
	 * @return ControllerInterface
	 */
	public function get(string $controller, array $dependencies = []): ControllerInterface;
}