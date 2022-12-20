<?php

namespace JayWolfeLib\Routing;

use JayWolfeLib\Result\ResultInterface;
use Symfony\Component\HttpFoundation\Request;

trait RouterTrait
{
	/** @var RouteCollectionInterface */
	private $routes;

	/** @var Request */
	private $request;

	/** @var ResultInterface */
	private $result;

	private function sanitize_route_method(RouteInterface $route, string $http_method): RouteInterface
	{
		if (empty($route->get('method')) || !(is_string($route->get('method')) || is_array($route->get('method')))) {
			$route->set('method', $http_method);
		}

		if (is_array($route->get('method'))) {
			$route->set('method', array_map('strtoupper', array_filter($route->get('method'), 'is_string')));

			return $route;
		}

		if (strtolower($route->get('method')) === 'any') {
			$route->set('method', $http_method);
		}

		$route->set('method', strtoupper($route->get('method')));

		return $route;
	}

	private function validate_route(RouteInterface $route, string $http_method): bool
	{
		$id = $route->id();
		$path = trim($route->get('path'), '/');
		$handler = $route->get('handler');

		return
			is_string($id) &&
			$id &&
			filter_var($path, FILTER_SANITIZE_URL) === $path &&
			in_array($http_method, (array) $route->get('method'), true) &&
			null !== $handler;
	}
}