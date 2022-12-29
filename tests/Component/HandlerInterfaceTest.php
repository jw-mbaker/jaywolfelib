<?php

namespace JayWolfeLib\Tests\Component;

use JayWolfeLib\Component\HandlerInterface;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Invoker\InvokerInterface;

class HandlerInterfaceTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
	}

	/**
	 * @group handler
	 */
	public function testCanAutowireInvoker()
	{
		$handler = new MockHandler(function(string $test) {
			$this->assertEquals($test, '123');
		});

		$this->container->call($handler, [\DI\get(InvokerInterface::class), '123']);
	}

	/**
	 * @group handler
	 */
	public function testCanPassContainerAsInvoker()
	{
		$handler = new MockHandler(function(string $test) {
			$this->assertEquals($test, 'abc');
		});

		$this->container->call($handler, [$this->container, 'abc']);
	}

	/**
	 * @group handler
	 */
	public function testCanPassMultipleValuesToCallable()
	{
		$handler = new MockHandler(function(string $test1, int $test2, float $test3) {
			$this->assertEquals($test1, '123');
			$this->assertEquals($test2, 123);
			$this->assertEquals($test3, 1.23);
		});

		$this->container->call($handler, [\DI\get(InvokerInterface::class), '123', 123, 1.23]);
	}
}