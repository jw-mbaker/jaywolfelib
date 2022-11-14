<?php

namespace JayWolfeLib\Router;

use JayWolfeLib\Factory\ControllerFactoryInterface;
use JayWolfeLib\Factory\ModelFactoryInterface;
use JayWolfeLib\Hooks\Hooks;

class Route
{
	/** @var ControllerFactoryInterface */
	protected $controllers;

	public function __construct(ControllerFactoryInterface $controllers)
	{
		$this->controllers = $controllers;
		$this->init();
	}

	protected function init()
	{

	}

	/**
	 * Identifies request type.
	 *
	 * @param string $route_type
	 * @return bool
	 */
	public function is_request(string $route_type): bool
	{
		switch ($route_type) {
			case RouteType::ANY:
				return true;
			case RouteType::ADMIN:
			case RouteType::ADMIN_WITH_POSSIBLE_AJAX:
				return is_admin();
			case RouteType::AJAX:
				return defined( 'DOING_AJAX' );
			case RouteType::CRON:
				return defined( 'DOING_CRON' );
			case RouteType::FRONTEND:
			case RouteType::FRONTEND_WITH_POSSIBLE_AJAX:
				return (!is_admin() || defined( 'DOING_AJAX' )) && !defined( 'DOING_CRON' ) && !defined( 'REST_REQUEST' );
			case RouteType::LATE_FRONTEND:
			case RouteType::LATE_FRONTEND_WITH_POSSIBLE_AJAX:
				return $this->is_request(RouteType::FRONTEND) || ( current_action() == 'wp' ) || ( did_action('wp') === 1 );
		}
	}
}