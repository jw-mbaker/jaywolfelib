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

trait FrontEndRouterTrait
{
	use RouterTrait;

	/** @var RouteCollector */
	private $collector;

	/** @var \Closure|null */
	private $dispatcher_factory;

	/** @var array<string, RouteInterface> */
	private $parsed_routes = [];
	
	public function set_collector(?RouteCollector $collector = null)
	{
		$this->collector ??= new RouteCollector(new Std(), new DataGenerator());
	}

	public function set_dispatcher_factory(?callable $factory = null)
	{
		$this->dispatcher_factory = $factory;
	}

	private function parse_routes(UriInterface $uri, string $http_method, callable $filter = null): int
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
			$path = '/' . trim($route->get('path'), '/');

			if ($path === '/' . trim($uri->getPath(), '/')) {
				$this->result = $this->finalize_route($route, [], $uri);
				unset($this->parsed_routes, $this->collector);
				break;
			}

			$this->collector->addRoute(strtoupper($route->get('method')), $path, $id);
		}

		unset($this->routes);

		return $parsed;
	}

	private function build_dispatcher(array $data): Dispatcher
	{
		$dispatcher = null;
		if (is_callable($this->dispatcher_factory)) {
			$dispatcher = call_user_funct($this->dispatcher_factory, $data);
		}

		$dispatcher instanceof Dispatcher or $dispatcher = new DefDispatcher($data);

		return $dispatcher;
	}
}