<?php

namespace JayWolfeLib\Tests\Component;

use JayWolfeLib\Component\CallerInterface;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Invoker\InvokerInterface;

class CallerInterfaceTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private $caller;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
		$this->caller = $this->container->get(MockCaller::class);
	}

	/**
	 * @group caller
	 * @group handler
	 */
	public function testCanAddAndGetHandler()
	{
		$handler = new MockHandler(function() {});
		$this->caller->addHandler('test', $handler);

		$this->assertSame($handler, $this->caller->getHandler('test'));
	}

	/**
	 * @group caller
	 * @group handler
	 */
	public function testCanInvokeHandler()
	{
		$handler = new MockHandler(function(string $test) {
			$this->assertEquals($test, '123');
		});

		$this->caller->addHandler('test', $handler);

		call_user_func([$this->caller, 'test'], '123');
	}

	/**
	 * @group caller
	 * @group handler
	 */
	public function testCanInvokeMultipleHandlers()
	{
		$handlers = [
			'test1' => new MockHandler(function (string $test) {
				$this->assertEquals($test, '123');
			}),
			'test2' => new MockHandler(function (string $str, bool $bool) {
				$this->assertEquals($str, 'test');
				$this->assertFalse($bool);
			}),
			'test3' => new MockHandler(function (float $float, int $int) {
				$this->assertEquals($float, 1.2);
				$this->assertEquals($int, 345);
			}),
			'test4' => new MockHandler(function (MockTypeHint $th, $test) {
				$this->assertEquals($test, 'test');
			}, ['map' => [\DI\get(MockTypeHint::class)]]),
			'test5' => new MockHandler(function (MockTypeHint $th) {
				$this->assertInstanceOf(MockTypeHint::class, $th);
			})
		];

		foreach ($handlers as $key => $handler) {
			$this->caller->addHandler($key, $handler);
		}

		call_user_func([$this->caller, 'test1'], '123');
		call_user_func([$this->caller, 'test2'], 'test', false);
		call_user_func([$this->caller, 'test3'], 1.2, 345);
		call_user_func([$this->caller, 'test4'], 'test');
		call_user_func([$this->caller, 'test5']);
	}

	/**
	 * @group caller
	 * @group handler
	 */
	public function testCanUseTypeHint()
	{
		$handler = new MockHandler(function (MockTypeHint $class) {
			$this->assertInstanceOf(MockTypeHint::class, $class);
		});

		$this->caller->addHandler('test', $handler);

		call_user_func([$this->caller, 'test']);
	}

	/**
	 * @group caller
	 * @group handler
	 */
	public function testCanMixValuesAndTypeHints()
	{
		$handler = new MockHandler(function (MockTypeHint $class, string $str) {
			$this->assertInstanceOf(MockTypeHint::class, $class);
			$this->assertEquals('test', $str);
		}, ['map' => [\DI\get(MockTypeHint::class)]]);

		$this->caller->addHandler('test', $handler);

		call_user_func([$this->caller, 'test'], 'test');
	}
}