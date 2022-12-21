<?php

namespace JayWolfeLib\Controllers;

use JayWolfeLib\Factory\ControllerFactoryInterface;
use JayWolfeLib\Views\ViewInterface;
use JayWolfeLib\Views\View;
use JayWolfeLib\Exception\InvalidController;
use JayWolfeLib\Container;

/**
 * @deprecated 3.0.0
 */
class Factory implements ControllerFactoryInterface
{
	/**
	 * The controller collection.
	 *
	 * @var ControllerCollection
	 */
	protected $controllers;

	/**
	 * Constructor.
	 *
	 * @param ControllerCollection $controllers
	 */
	public function __construct(ControllerCollection $controllers)
	{
		$this->controllers = $controllers;
	}

	/**
	 * Get a controller instance from the container.
	 * Instantiates it if it does not exist.
	 *
	 * @param string $controller
	 * @param ViewInterface|null $view
	 * @return ControllerInterface
	 * @throws InvalidController
	 */
	public function get(string $controller, ?ViewInterface $view = null, ...$dependencies): ControllerInterface
	{
		if (!class_exists($controller)) {
			$this->throw_controller_not_found($controller);
		}

		if (null === $this->controllers->get($controller)) {
			$class = new \ReflectionClass($controller);
			if (!$class->implementsInterface(ControllerInterface::class)) {
				$this->throw_controller_not_implement_interface($controller);
			}

			return $this->create($controller, $view, ...$dependencies);
		}

		return $this->controllers->get($controller);
	}

	/**
	 * Instantiate a controller and trigger its init method.
	 *
	 * @param string $controller
	 * @param ViewInterface $view
	 * @param mixed $dependencies
	 * @return ControllerInterface
	 * @throws InvalidController
	 */
	public function create(string $controller, ViewInterface $view, ...$dependencies): ControllerInterface
	{
		if (!class_exists($controller)) {
			$this->throw_controller_not_found($controller);
		}

		if (null !== $this->controllers->get($controller)) {
			return $this->get($controller);
		}

		$class = new \ReflectionClass($controller);
		if (!$class->implementsInterface(ControllerInterface::class)) {
			$this->throw_controller_not_implement_interface($controller);
		}

		$this->controllers->add($controller, new $controller($view, ...$dependencies));

		return $this->controllers->get($controller);
	}

	/**
	 * Get the controller collection.
	 *
	 * @return ControllerCollection
	 */
	public function get_controllers(): ControllerCollection
	{
		return $this->controllers;
	}

	protected function throw_controller_not_found(string $controller)
	{
		throw new InvalidController($controller . ' not found.');
	}

	protected function throw_controller_not_implement_interface(string $controller)
	{
		throw new InvalidController($controller . ' does not implement ' . ControllerInterface::class);
	}
}