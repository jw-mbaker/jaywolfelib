<?php

namespace JayWolfeLib\Route;

use JayWolfeLib\Collection\AbstractCollection;

class RouteCollection extends AbstractCollection implements RouteCollectionInterface
{
	/**
	 * @var array<string, Route>
	 */
	private $routes = [];

	public function add(string $name, RouteInterface $route)
	{
		$this->routes[$name] = $route;
	}

	public function addRoute(RouteInterface $route)
	{
		$this->add($route->id(), $route);
	}

	public function all(): array
	{
		return $this->routes;
	}

	public function get(string $name): ?RouteInterface
	{
		return $this->routes[$name] ?? null;
	}

	/**
	 * Removes a route or an array of routes by name from the collection.
	 *
	 * @param string|string[] $name
	 */
	public function remove($name)
	{
		foreach ((array) $name as $n) {
			unset($this->routes[$n]);
		}
	}
}