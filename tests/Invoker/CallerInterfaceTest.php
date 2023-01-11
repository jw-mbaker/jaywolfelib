<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Invoker;

use JayWolfeLib\Invoker\CallerInterface;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Invoker\InvokerInterface;

class CallerInterfaceTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private MockHandlerCollection $collection;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
		$this->collection = new MockHandlerCollection($this->container);
	}

	/**
	 * @group caller
	 * @group handler
	 */
	public function testCanAddHandler()
	{
		$handler = $this->createMockHandler();

		$this->collection->addHandler($handler);

		$this->assertSame($handler, $this->collection->getById($handler->id()));
	}

	/**
	 * @group caller
	 * @group handler
	 */
	public function testCanInvokeHandler()
	{
		$handler = MockHandler::create([
			MockHandler::NAME => 'test',
			MockHandler::CALLABLE => function(string $test) {
				$this->assertSame('123', $test);
			}
		]);

		$this->collection->addHandler($handler);

		call_user_func([$this->collection, (string) $handler->id()], '123');
	}

	/**
	 * @group caller
	 * @group handler
	 * @dataProvider handlerProvider
	 */
	public function testCanInvokeMultipleHandlers(array $handlers)
	{
		foreach ($handlers as $handler) {
			$this->collection->addHandler($handler);
		}

		call_user_func([$this->collection, (string) $handlers[0]->id()], '123');
		call_user_func([$this->collection, (string) $handlers[1]->id()], 'test', false);
		call_user_func([$this->collection, (string) $handlers[2]->id()], 1.2, 345);
		call_user_func([$this->collection, (string) $handlers[3]->id()], 'test');
		call_user_func([$this->collection, (string) $handlers[4]->id()]);
	}

	public function handlerProvider(): array
	{
		$handlers = [
			MockHandler::create([
				MockHandler::NAME => 'test1',
				MockHandler::CALLABLE => function(string $test) {
					$this->assertSame('123', $test);
				}
			]),
			MockHandler::create([
				MockHandler::NAME => 'test2',
				MockHandler::CALLABLE => function(string $str, bool $bool) {
					$this->assertSame('test', $str);
					$this->assertFalse($bool);
				}
			]),
			MockHandler::create([
				MockHandler::NAME => 'test3',
				MockHandler::CALLABLE => function(float $float, int $int) {
					$this->assertSame(1.2, $float);
					$this->assertSame(345, $int);
				}
			]),
			MockHandler::create([
				MockHandler::NAME => 'test4',
				MockHandler::CALLABLE => function(MockTypeHint $th, string $test) {
					$this->assertSame('test', $test);
				},
				MockHandler::MAP => [\DI\get(MockTypeHint::class)]
			]),
			MockHandler::create([
				MockHandler::NAME => 'test5',
				MockHandler::CALLABLE => function(MockTypeHint $th) {
					$this->assertInstanceOf(MockTypeHint::class, $th);
				}
			])
		];

		return [ [ $handlers ] ];
	}

	/**
	 * @group caller
	 * @group handler
	 */
	public function testCanUseTypeHint()
	{
		$handler = MockHandler::create([
			MockHandler::NAME => 'test',
			MockHandler::CALLABLE => function(MockTypeHint $class) {
				$this->assertInstanceOf(MockTypeHint::class, $class);
			}
		]);

		$this->collection->addHandler($handler);

		call_user_func([$this->collection, (string) $handler->id()]);
	}

	/**
	 * @group caller
	 * @group handler
	 */
	public function testCanMixScalarValuesAndTypeHints()
	{
		$handler = MockHandler::create([
			MockHandler::NAME => 'test',
			MockHandler::CALLABLE => function(MockTypeHint $class, string $str) {
				$this->assertInstanceOf(MockTypeHint::class, $class);
				$this->assertSame('test', $str);
			},
			MockHandler::MAP => [\DI\get(MockTypeHint::class)]
		]);

		$this->collection->addHandler($handler);

		call_user_func([$this->collection, (string) $handler->id()], 'test');
	}

	private function createMockHandler(): MockHandler
	{
		return MockHandler::create([
			MockHandler::NAME => 'test',
			MockHandler::CALLABLE => function() {}
		]);
	}
}