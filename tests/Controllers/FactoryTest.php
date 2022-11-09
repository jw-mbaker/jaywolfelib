<?php

namespace JayWolfeLib\Tests\Controllers;

use JayWolfeLib\Container;
use JayWolfeLib\Controllers\Factory;
use JayWolfeLib\Controllers\ControllerInterface;
use JayWolfeLib\Exception\InvalidController;
use WP_Mock;
use Mockery;

class FactoryTest extends WP_Mock\Tools\TestCase
{
	public function setUp(): void
	{
		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	public function testCanCreateControllerInstance(): void
	{
		WP_Mock::passthruFunction('sanitize_key');

		$factory = new Factory(new Container());

		$mock = $factory->create(MockClass::class);

		$this->assertInstanceOf(MockClass::class, $mock);
	}

	public function testCanGetControllerInstance(): void
	{
		WP_Mock::passthruFunction('sanitize_key');

		$factory = new Factory(new Container());

		$mock = $factory->create(MockClass::class);

		$this->assertSame($mock, $factory->get(MockClass::class));
	}

	public function testCanTriggerControllerInit(): void
	{
		WP_Mock::passthruFunction('sanitize_key');

		$factory = new Factory(new Container());

		$mock = $factory->create(MockClass::class);

		$this->assertEquals($mock->val, 1);
	}

	public function testThrowsInvalidController(): void
	{
		WP_Mock::passthruFunction('sanitize_key');

		$factory = new Factory(new Container());

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