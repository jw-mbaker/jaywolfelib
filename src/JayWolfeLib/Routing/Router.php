<?php

namespace JayWolfeLib\Routing;

use JayWolfeLib\Result\ResultInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\Request;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\MarkBased as DefDispatcher;
use FastRoute\RouteCollector;
use FastRoute\DataGenerator\MarkBased as DefDataGenerator;
use FastRoute\RouteParser\Std;

use function JayWolfeLib\validate_bool;

class Router implements RouterInterface
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
			!$this->parse_routes($uri, $http_method, function($route) {
				return $route instanceof QueryRoute || $route instanceof RedirectRoute;
			})
		) {
			$this->result = new Result(['route' => null]);

			return $this->result;
		}

		if ($this->result instanceof ResultInterface) {
			return $this->result;
		}

		$dispatcher = $this->build_dispatcher($this->collector->getData());
		unset($this->collector);

		$uri_path = sprintf('/%s', trim($uri->getPath(), '/'));
		$route_info = $dispatcher->dispatch($http_method, $uri_path ?: '/');
		if ($route_info[0] === Dispatcher::FOUND) {
			$route = $this->parsed_routes[$route_info[1]];
			$vars = $route_info[2];

			$this->result = $this->finalize_route($route, $vars, $uri);
		}

		$this->result or $this->result = new Result(['route' => null]);

		unset($this->parsed_routes);

		return $this->result;
	}

	private function finalize_route(RouteInterface $route, array $vars, UriInterface $uri): ResultInterface
	{
		$queryVars = explode('&', $uri->getQuery());
		$vars = array_merge($vars, $queryVars);
		$vars_original = $vars;

		$result = null;
		switch (true) {
			case (is_callable($route->get('vars'))):
				$cb = $route->get('vars');
				$route_vars = $cb($vars, $uri);
				if (is_array($route_vars)) {
					$vars = $route_vars;
				}
				$route_vars instanceof Result and $result = $route_vars;
				break;

			case (is_array($route->get('vars'))):
				$vars = array_merge($route->get('vars'), $vars);
				break;
			
			case ($route->get('vars') instanceof Result):
				$result = $route->get('vars');
				break;
		}

		if ($result instanceof Result) {
			return $result;
		}

		if (!empty($route->get('default_vars')) && is_array($route->get('default_vars'))) {
			$vars = array_merge($route->get('default_vars'), $vars);
		}

		$vars = $this->ensure_preview_vars($vars, $queryVars);
		$no_template = validate_bool($route->get('no_template'));

		return new Result([
			'vars' => (array) $vars,
			'matches' => (array) $vars_original,
			'route' => $route->id(),
			'path' => $route->get('path'),
			'handler' => $route->get('handler'),
			'dependencies' => $route->get('dependencies'),
			'template' => $no_template ? false : $route->get('template')
		]);
	}

	private function ensure_preview_vars(array $vars, array $uri_vars): array
	{
		if (!is_user_logged_in()) {
			return $vars;
		}

		foreach (['preview', 'preview_id', 'preview_nonce'] as $var) {
			if (!isset($vars[$var]) && isset($uri_vars[$var])) {
				$vars[$var] = $uri_vars[$var];
			}
		}

		return $vars;
	}
}