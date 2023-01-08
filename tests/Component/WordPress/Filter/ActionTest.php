<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Component\WordPress\Filter;

use JayWolfeLib\Component\WordPress\Filter\Action;
use JayWolfeLib\Tests\Invoker\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Invoker\InvokerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WP_Mock;
use WP_Mock\InvokedFilterValue;
use Mockery;

class ActionTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group action
	 */
	public function testCanInvokeActionWithNoArgs()
	{
		$action = Action::create([
			Action::HOOK => 'test',
			Action::CALLABLE => function() {
				$this->assertTrue(true);
			}
		]);

		WP_Mock::onAction($action->hook())
			->with(null)
			->perform(function() use ($action) {
				$this->container->call($action);
			});

		do_action($action->hook());
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group action
	 */
	public function testCanInvokeActionWithArgs()
	{
		$action = Action::create([
			Action::HOOK => 'test',
			Action::CALLABLE => function(string $test, int $testInt) {
				$this->assertEquals('test', $test);
				$this->assertEquals(123, $testInt);
			},
			Action::NUM_ARGS => 2
		]);

		WP_Mock::onAction($action->hook())
			->with('test', 123)
			->perform(function() use ($action) {
				$this->container->call($action, [$this->container, 'test', 123]);
			});

		do_action($action->hook(), 'test', 123);
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group action
	 */
	public function testCanInvokeActionWithTypeHint()
	{
		$action = Action::create([
			Action::HOOK => 'test',
			Action::CALLABLE => function(MockTypeHint $th) {
				$this->assertInstanceOf(MockTypeHint::class, $th);
			}
		]);

		WP_Mock::onAction($action->hook())
			->with(null)
			->perform(function() use ($action) {
				$this->container->call($action);
			});

		do_action($action->hook());
	}

	public function testCanInvokeActionWithTypeHintAndArgs()
	{
		$action = Action::create([
			Action::HOOK => 'test',
			Action::CALLABLE => function(MockTypeHint $th, string $test, bool $bool) {
				$this->assertEquals('test', $test);
				$this->assertTrue($bool);
			},
			Action::MAP => [\DI\get(MockTypeHint::class)],
			Action::NUM_ARGS => 2
		]);

		WP_Mock::onAction($action->hook())
			->with('test', true)
			->perform(function() use ($action) {
				$this->container->call($action, [$this->container, \DI\get(MockTypeHint::class), 'test', true]);
			});

		do_action($action->hook(), 'test', true);
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group action
	 */
	public function testActionCanHandleResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$action = Action::create([
			Action::HOOK => 'test',
			Action::CALLABLE => function() use ($response) {
				$this->assertTrue(true);
				return $response;
			}
		]);

		WP_Mock::onAction($action->hook())
			->with(null)
			->perform(function() use ($action) {
				$this->container->call($action);
			});

		do_action($action->hook());
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group action
	 */
	public function testActionCanTakeRequestObjectAndHandleResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$this->container->set(Request::class, Mockery::mock(Request::class));

		$action = Action::create([
			Action::HOOK => 'test',
			Action::CALLABLE => function(Request $request) use ($response) {
				$this->assertInstanceOf(Request::class, $request);
				return $response;
			}
		]);

		WP_Mock::onAction($action->hook())
			->with(null)
			->perform(function() use ($action) {
				$this->container->call($action);
			});

		do_action($action->hook());
	}
}