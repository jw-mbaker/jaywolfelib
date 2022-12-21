<?php

namespace JayWolfeLib\Component\Routing;

use JayWolfeLib\Collection\AbstractCollection;

class RouteCollection extends AbstractCollection implements RouteCollectionInterface
{
	/**
	 * @var array<string, Route>
	 */
	private $routes = [];

	public function add(string $action, RouteInterface $route)
	{
		$this->routes[$name] = $route;

		switch ($route->get('type')) {
			case 'action':
				$this->add_action($action, $route);
				break;
			case 'filter':
				$this->add_filter($action, $route);
				break;
		}
	}

	public function addRoute(RouteInterface $route)
	{
		$this->add($route->id, $route);
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

	private function add_action(string $action, RouteInterface $route)
	{
		add_action($route->action(), [$this, $route->id()]);

		if ($route instanceof AjaxRoute && $route->get('nopriv')) {
			$action = 'wp_ajax_nopriv_' . ltrim($route->action(), 'wp_ajax_');
			add_action($action, [$this, $route->id()]);
		}
	}

	private function add_filter(string $filter, RouteInterface $route)
	{
		if ($route instanceof AjaxRoute) {
			throw new \BadMethodCallException(
				sprintf('Cannot add filter with %s. Use %s instead.', AjaxRoute::class, Route::class)
			);
		}
	}

	/**
	 * Invoke the route's action.
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments)
	{
		return $this->invoker->call($name, $arguments);
	}
}