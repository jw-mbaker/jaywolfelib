<?php

namespace JayWolfeLib\Controllers;

use JayWolfeLib\Factory\ControllerFactoryInterface;
use JayWolfeLib\Exception\InvalidController;
use JayWolfeLib\Container;

class Factory implements ControllerFactoryInterface
{
	protected $controllerContainer;

	/**
	 * Constructor.
	 *
	 * @param Container $controllerContainer
	 */
	public function __construct(Container $controllerContainer)
	{
		$this->controllerContainer = $controllerContainer;
	}

	/**
	 * Get a controller instance from the container.
	 * Instantiates it if it does not exist.
	 *
	 * @param string $controller
	 * @param array $dependencies
	 * @return ControllerInterface
	 * @throws InvalidController
	 */
	public function get(string $controller, array $dependencies = []): ControllerInterface
	{
		if (!class_exists($controller)) {
			$this->throw_controller_not_found($controller);
		}

		$key = sanitize_key($controller);

		if (!isset($this->controllerContainer[$key])) {
			$class = new \ReflectionClass($controller);
			if (!$class->implementsInterface(ControllerInterface::class)) {
				$this->throw_controller_not_implement_interface($controller);
			}

			return $this->create($controller, $dependencies);
		}

		return $this->controllerContainer[$key];
	}

	/**
	 * Instantiate a controller and trigger its init method.
	 *
	 * @param string $controller
	 * @param array $dependencies
	 * @return ControllerInterface
	 * @throws InvalidController
	 */
	public function create(string $controller, array $dependencies = []): ControllerInterface
	{
		if (!class_exists($controller)) {
			$this->throw_controller_not_found($controller);
		}

		$key = sanitize_key($controller);

		if (isset($this->controllerContainer[$key])) {
			return $this->get($controller);
		}

		$class = new \ReflectionClass($controller);
		if (!$class->implementsInterface(ControllerInterface::class)) {
			$this->throw_controller_not_implement_interface($controller);
		}

		$this->controllerContainer->init($key, $controller, ...$dependencies);

		return $this->controllerContainer[$key];
	}

	/**
	 * Get the controller container.
	 *
	 * @return Container
	 */
	public function get_container(): Container
	{
		return $this->controllerContainer;
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