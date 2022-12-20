<?php

namespace JayWolfeLib\Route;

trait RouterTrait
{
	/**
	 * Initialize the router.
	 *
	 * @param Router $router
	 * @param string $routes
	 * @return void
	 */
	public function init_router(Router $router, string $routes): void
	{
		if (!file_exists($routes)) {
			throw new \InvalidArgumentException("Routes file $routes not found.");
		}

		include_once $routes;
	}
}