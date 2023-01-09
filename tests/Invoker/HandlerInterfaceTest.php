<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Invoker;

use JayWolfeLib\Invoker\HandlerInterface;
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
	public function testCanPassContainerAsInvoker()
	{
		$handler = MockHandler::create([
			MockHandler::NAME => 'test',
			MockHandler::CALLABLE => function(string $test) {
				$this->assertSame('abc', $test);
			}
		]);

		$this->container->call($handler, [$this->container, 'abc']);
	}

	/**
	 * @group handler
	 */
	public function testCanPassMultipleValuesToCallable()
	{
		$handler = MockHandler::create([
			MockHandler::NAME => 'test',
			MockHandler::CALLABLE => function(string $test1, int $test2, float $test3) {
				$this->assertSame('123', $test1);
				$this->assertSame(123, $test2);
				$this->assertSame(1.23, $test3);
			}
		]);

		$this->container->call($handler, [$this->container, '123', 123, 1.23]);
	}
}