<?php

namespace JayWolfeLib\Factory;

use JayWolfeLib\Controllers\ControllerInterface;
use JayWolfeLib\Views\ViewInterface;

interface ControllerFactoryInterface extends BaseFactoryInterface
{
	/**
	 * Get a controller instance from the container.
	 * Instantiates it if it does not exist.
	 *
	 * @param string $controller
	 * @param ViewInterface|null $view
	 * @param mixed $dependencies
	 * @return ControllerInterface
	 */
	public function get(string $controller, ?ViewInterface $view = null, ...$dependencies): ControllerInterface;

	/**
	 * Instantiate a controller and trigger its init method.
	 *
	 * @param string $controller
	 * @param ViewInterface $view
	 * @param mixed $dependencies
	 * @return ControllerInterface
	 */
	public function create(string $controller, ViewInterface $view, ...$dependencies): ControllerInterface;
}