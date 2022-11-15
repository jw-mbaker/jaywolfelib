<?php

namespace JayWolfeLib\Tests\Controllers;

use JayWolfeLib\Container;
use JayWolfeLib\Controllers\Factory as ControllerFactory;
use JayWolfeLib\Controllers\ControllerInterface;
use JayWolfeLib\Models\Factory as ModelFactory;
use JayWolfeLib\Config\Config;
use JayWolfeLib\Views\View;
use JayWolfeLib\Input;
use JayWolfeLib\Exception\InvalidController;
use WP_Mock;
use Mockery;

class FactoryTest extends WP_Mock\Tools\TestCase
{
	private $mainContainer;
	private $view;

	public function setUp(): void
	{
		WP_Mock::setUp();
		WP_Mock::passthruFunction('sanitize_key');
		$this->mainContainer = new Container();
		$this->view = Mockery::mock(View::class);
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	public function testCanCreateControllerInstance(): void
	{
		$factory = new ControllerFactory(new Container(), $this->mainContainer);

		$mock = $factory->create(MockClass::class, $this->view);

		$this->assertInstanceOf(MockClass::class, $mock);
	}

	public function testCanGetControllerInstance(): void
	{
		$factory = new ControllerFactory(new Container(), $this->mainContainer);

		$mock = $factory->create(MockClass::class, $this->view);

		$this->assertSame($mock, $factory->get(MockClass::class));
	}

	public function testCanTriggerControllerInit(): void
	{
		$factory = new ControllerFactory(new Container(), $this->mainContainer);

		$mock = $factory->create(MockClass::class, $this->view);

		$this->assertEquals($mock->val, 1);
	}

	public function testThrowsInvalidController(): void
	{
		$factory = new ControllerFactory(new Container(), $this->mainContainer);

		$this->expectException(InvalidController::class);
		$factory->get('\Test');

		$factory->get(get_class(
			new class() {}
		));

		$factory->create('\Test');
		$factory->create(get_class(
			new class() {}
		));
	}
}