<?php

namespace JayWolfeLib\Routing;

use JayWolfeLib\Result\ResultInterface;
use JayWolfeLib\Result\ApiResult;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\Request;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\MarkBased as DefDispatcher;
use FastRoute\RouteCollector;
use FastRoute\DataGenerator\MarkBased as DefDataGenerator;
use FastRoute\RouteParser\Std;

class ApiRouter implements RouterInterface
{
	use FrontEndRouterTrait;

	public function __construct(
		Request $request,
		RouteCollectionInterface $routes,
		RouteCollector $collector = null,
		callable $dispatcher_factory = null
	) {
		$this->request = $request;
		$this->routes = $routes;
		$this->set_collector($collector);
		$this->set_dispatcher_factory($dispatcher_factory);
	}

	public function match(UriInterface $uri, string $http_method): ResultInterface
	{
		if ($this->result instanceof ResultInterface) {
			return $this->result;
		}

		if (
			!count($this->routes) ||
			!$this->parse_routes($uri, $http_method, fn($route) => $route instanceof ApiRoute)
		) {
			$this->result = new ApiResult(['route' => null]);

			return $this->result;
		}

		if ($this->result instanceof ResultInterface) {
			return $this->result;
		}

		$dispatcher = $this->build_dispatcher($this->collector->getData());
		unset($this->collector);

		$uri_path = sprintf('/%s', trim($uri->getPath(), '/'));
		$route_info = $dispatcher->dispatch($http_method, $uri_path ?: '/api');
		if ($route_info[0] === Dispatcher::FOUND) {
			$route = $this->parsed_routes[$route_info[1]];
			$vars = $route_info[2];

			$this->result = $this->finalize_route($route, $vars, $uri);
		}

		$this->result or $this->result = new ApiResult(['route' => null]);

		unset($this->parsed_routes);

		return $this->result;
	}

	private function validate_route(RouteInterface $route, string $http_method): bool
	{
		$id = $route->id();
		$path = trim($route->get('path'), '/');
		$handler = $route->get('handler');
		$action = $route->get('action');
		$key = $route->get('api_key');
		$res = true;

		if (!is_string($id) || !$id) {
			$res = false;
		}

		if (filter_var($path, FILTER_SANITIZE_URL) !== $path) {
			$res = false;
		}

		if (!in_array($http_method, (array) $route->get('method'), true)) {
			$res = false;
		}

		if (is_string($key) && !empty($key)) {
			if ($this->request->get('key') !== $key) {
				$res = false;
			}
		}

		if (null === $handler) {
			$res = false;
		}

		if (!is_string($action) || !$action || $this->request->get('action') !== $action) {
			$res = false;
		}

		return $res;
	}

	private function finalize_route(RouteInterface $route, array $vars, UriInterface $uri): ResultInterface
	{
		$queryVars = explode('&', $uri->getQuery());
		$vars = array_merge($vars, $queryVars);
		$vars_original = $vars;

		$result = null;

		if (!empty($route->get('default_vars')) && is_array($route->get('default_vars'))) {
			$vars = array_merge($route->get('default_vars'), $vars);
		}

		return new ApiResult([
			'vars' => (array) $vars,
			'matches' => (array) $vars_original,
			'route' => $route->id(),
			'path' => $route->get('path'),
			'handler' => $route->get('handler'),
			'dependencies' => $route->get('dependencies'),
			'action' => $route->get('action'),
			'api_key' => $route->get('api_key'),
			'request' => $this->request
		]);
	}
}