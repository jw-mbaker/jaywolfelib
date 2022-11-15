<?php

namespace JayWolfeLib\Tests\Hooks;

use JayWolfeLib\Hooks\Handler;
use JayWolfeLib\Input;
use JayWolfeLib\Container;
use JayWolfeLib\Models\Factory as ModelFactory;
use WP_Mock;
use Mockery;

class HandlerTest extends WP_Mock\Tools\TestCase
{
	private $input;

	public function setUp(): void
	{
		WP_Mock::setUp();
		$this->input = Mockery::mock(Input::class);
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	public function testCanInvokeCallback(): void
	{
		$handler = new Handler($this->input, function() {
			$this->assertTrue(true);
		});

		call_user_func($handler);
	}

	public function testCanPassDependency(): void
	{
		$handler = new Handler($this->input, function(Input $input, $test) {
			$this->assertEquals($test, 'test');
		});

		$handler->with('test');

		call_user_func($handler);
	}

	/**
	 * @depends testCanPassDependency
	 *
	 * @return void
	 */
	public function testCanPassClassAsString(): void
	{
		$handler = new Handler($this->input, function(Input $input, $obj) {
			$this->assertInstanceOf(\stdClass::class, $obj);
		});

		$handler->with('\stdClass');

		call_user_func($handler);
	}

	/**
	 * @depends testCanPassDependency
	 *
	 * @return void
	 */
	public function testCanPassContainerKey(): void
	{
		$container = new Container();
		$container->set('test', 1);

		$handler = new Handler($this->input, function(Input $input, $test) {
			$this->assertEquals($test, 1);
		});

		$handler->with([$container, 'test']);

		call_user_func($handler);
	}

	/**
	 * @depends testCanPassDependency
	 *
	 * @return void
	 */
	public function testCanPassCallable(): void
	{
		$handler = new Handler($this->input, function(Input $input, $test) {
			$this->assertEquals($test, 123);
		});

		$handler->with(fn() => 123);

		call_user_func($handler);
	}

	/**
	 * @depends testCanPassDependency
	 * 
	 * @return void
	 */
	public function testCanPassFactoryKey(): void
	{
		$factory = Mockery::mock(ModelFactory::class);
		$mock = Mockery::mock(\JayWolfeLib\Models\ModelInterface::class);

		$factory->expects()->get('mock')->andReturn($mock);

		$handler = new Handler($this->input, function(Input $input, $mock) {
			$this->assertInstanceOf(\JayWolfeLib\Models\ModelInterface::class, $mock);
		});

		$handler->with([$factory, 'mock']);

		call_user_func($handler);
	}
}