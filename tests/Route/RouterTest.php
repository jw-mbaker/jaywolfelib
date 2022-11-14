<?php

namespace JayWolfeLib\Tests\Route;

use JayWolfeLib\Route\Router;
use JayWolfeLib\Route\RouteType;
use JayWolfeLib\Controllers\Factory as ControllerFactory;
use JayWolfeLib\Controllers\Controller;
use JayWolfeLib\Config\Config;
use WP_Mock;
use WP_Mock\Matcher\AnyInstance;
use Mockery;

class RouterTest extends WP_Mock\Tools\TestCase
{
	private $controllers;
	private $config;

	public function setUp(): void
	{
		WP_Mock::setUp();
		WP_Mock::userFunction('did_action', ['return' => false]);
		$this->controllers = Mockery::mock(ControllerFactory::class);
		$this->config = Mockery::mock(Config::class);
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @doesNotPerformAssertions
	 *
	 * @return void
	 */
	public function testHooksAdded(): void
	{
		$instance = new AnyInstance(Router::class);

		WP_Mock::expectActionAdded('init', [$instance, 'register_generic_routes']);
		WP_Mock::expectActionAdded('wp', [$instance, 'register_late_frontend_routes']);

		$router = new Router($this->controllers, $this->config);
	}

	public function testRegisterRouteOfType()
	{
		$router = new Router($this->controllers, $this->config);

		$router->register_route_of_type(RouteType::ADMIN);

		$this->assertEquals($router->route_type_to_register, RouteType::ADMIN);
	}

	public function testCanRegisterController()
	{
		WP_Mock::userFunction('is_admin', ['return' => true]);

		$router = new Router($this->controllers, $this->config);

		$controller = Mockery::mock(Controller::class);

		$this->controllers->shouldReceive('create')->once()->andReturn($controller);

		$router->register_route_of_type(RouteType::ADMIN);
		$this->assertEquals($router->route_type_to_register, RouteType::ADMIN);

		$router->with_controller(Controller::class);

		$router->register_generic_routes();
	}
}