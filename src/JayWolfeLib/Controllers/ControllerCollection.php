<?php

namespace JayWolfeLib\Controllers;

use JayWolfeLib\Collection\AbstractCollection;
use JayWolfeLib\View\ViewInterface;
use JayWolfeLib\Exception\InvalidController;

class ControllerCollection extends AbstractCollection
{
	/**
	 * @var array<string, ControllerInterface>
	 */
	private $controllers = [];

	public function add(string $class, ControllerInterface $controller)
	{
		$this->controllers[$class] = $controller;
	}

	public function all(): array
	{
		return $this->controllers;
	}

	/**
	 * @param string $class
	 * @return ControllerInterface|null
	 */
	public function get(string $class): ?ControllerInterface
	{
		return $this->controllers[$class] ?? null;
	}

	/**
	 * Removes a controller or an array of controllers by name from the collection.
	 *
	 * @param string|string[] $class
	 */
	public function remove($class)
	{
		foreach ((array) $class as $c) {
			unset($this->controllers[$c]);
		}
	}
}