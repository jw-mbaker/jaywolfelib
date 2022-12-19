<?php

namespace JayWolfeLib\Route;

use Symfony\Component\HttpFoundation\Request;
use Closure;

class Dispatcher
{
	/**
	 * @var RouteCollection
	 */
	private $routes;

	public function __construct(RouteCollection $routes)
	{
		$this->routes = $routes;
	}

	public function dispatch()
	{

	}

	public function get_routes(): RouteCollection
	{
		return $this->routes;
	}
}