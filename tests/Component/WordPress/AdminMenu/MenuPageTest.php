<?php

namespace JayWolfeLib\Tests\Component\WordPress\AdminMenu;

use JayWolfeLib\Component\WordPress\AdminMenu\MenuPage;
use JayWolfeLib\Tests\Component\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WP_Mock;
use Mockery;

class MenuPageTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
		$this->container->set(Request::class, Mockery::mock(Request::class));
		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 */
	public function testCanInvokeMenuPage()
	{
		$mp = new MenuPage('test', function() {
			$this->assertTrue(true);
		});

		$this->container->call($mp);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 */
	public function testCanInvokeMenuPageWithTypeHint()
	{
		$mp = new MenuPage('test', function(Request $request, MockTypeHint $th) {
			$this->assertInstanceOf(Request::class, $request);
			$this->assertInstanceOf(MockTypeHint::class, $th);
		});

		$this->container->call($mp);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 */
	public function testMenuPageCanHandleResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$mp = new MenuPage('test', function() use ($response) {
			$this->assertTrue(true);
			return $response;
		});

		$this->container->call($mp);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 */
	public function testMenuPageCanTakeRequestObjectAndHandleResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$mp = new MenuPage('test', function(Request $request) use ($response) {
			$this->assertInstanceOf(Request::class, $request);
			return $response;
		});

		$this->container->call($mp);
	}
}