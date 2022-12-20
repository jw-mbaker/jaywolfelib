<?php

namespace JayWolfeLib\Routing;

use JayWolfeLib\Result\ResultInterface;
use Symfony\Component\HttpFoundation\Request;

class AjaxRouter implements RouterInterface
{
	use RouterTrait;

	public function __construct(Request $request, RouteCollectionInterface $routes)
	{
		$this->request = $request;
		$this->routes = $routes;
	}

	public function match(string $http_method): ResultInterface
	{
		if ($this->result instanceof ResultInterface) {
			return $this->result;
		}

		if (
			!count($this->routes) ||
			!$this->parse_routes($http_method, fn($route) => $route instanceof AjaxRoute)
		) {
			$this->result = new AjaxResult(['route' => null]);

			return $this->result;
		}

		if ($this->result instanceof ResultInterface) {
			return $this->result;
		}
	}

	private function parse_routes(string $http_method, callable $filter = null): int
	{
		$parsed = 0;

		if (is_callable($filter)) {
			$routes = array_filter($this->routes, $filter);
		} else {
			$routes = $this->routes;
		}

		foreach ($routes as $key => $route) {
			$route = $this->sanitize_route_method($route, $http_method);

			if (!$this->validate_route($route, $http_method)) {
				continue;
			}

			$parsed++;

			$id = $route->id();
			$this->parsed_routes[$id] = $route;

			$this->result = $this->finalize_route($route, []);
		}
	}

	private function validate_route(RouteInterface $route, string $http_method)
	{
		$id = $route->id();
		$handler = $route->get('handler');
		$action = $route->get('action');
		$res = true;

		if (!is_string($id) || !$id) {
			$res = false;
		}

		if (!in_array($http_method, (array) $route->get('method'), true)) {
			$res = false;
		}

		if (null === $handler) {
			$res = false;
		}

		if (!is_string($action) || !$action || $this->request->get('action') !== $action) {
			$res = false;
		}

		return $res;
	}

	private function finalize_route(RouteInterface $route, array $vars): ResultInterface
	{
		return new AjaxResult([
			'route' => $route->id(),
			'handler' => $route->get('handler'),
			'dependencies' => $route->get('dependencies'),
			'action' => $route->get('action'),
			'nopriv' => $route->get('nopriv'),
			'request' => $this->request
		]);
	}
}