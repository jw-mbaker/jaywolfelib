<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\WordPress\Filter;

use JayWolfeLib\WordPress\Filter\Filter;
use JayWolfeLib\WordPress\Filter\HookId;
use JayWolfeLib\Tests\Invoker\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Invoker\InvokerInterface;
use WP_Mock;
use WP_Mock\InvokedFilterValue;
use Mockery;

class FilterTest extends \WP_Mock\Tools\TestCase
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
	}

	/**
	 * @group hook
	 * @group filter
	 * @group wordpress
	 */
	public function testCanInvokeFilter()
	{
		$filter = Filter::create([
			Filter::HOOK => 'test',
			Filter::CALLABLE => function(string $test) {
				$this->assertEquals($test, 'test');
			}
		]);

		WP_Mock::onFilter($filter->hook())
			->with('test')
			->reply(new InvokedFilterValue(function(string $test) use ($filter) {
				return $this->container->call($filter, [$this->container, $test]);
			}));
		
		apply_filters($filter->hook(), 'test');
	}

	/**
	 * @group hook
	 * @group filter
	 * @group wordpress
	 */
	public function testCanInvokeFilterWithTypeHint()
	{
		$filter = Filter::create([
			Filter::HOOK => 'test',
			Filter::CALLABLE => function(MockTypeHint $th, string $test) {
				$this->assertEquals($test, 'test');
			},
			Filter::MAP => [\Di\get(MockTypeHint::class)]
		]);

		WP_Mock::onFilter($filter->hook())
			->with('test')
			->reply(new InvokedFilterValue(function(string $test) use ($filter) {
				return $this->container->call($filter, [$this->container, ...$filter->map(), $test]);
			}));
		
		apply_filters($filter->hook(), 'test');
	}

	/**
	 * @group hook
	 * @group filter
	 * @group wordpress
	 */
	public function testCanGetId()
	{
		$filter = Filter::create([
			Filter::HOOK => 'test',
			Filter::CALLABLE => function() {}
		]);

		$this->assertInstanceOf(HookId::class, $filter->id());
	}

	/**
	 * @group hook
	 * @group filter
	 * @group wordpress
	 */
	public function testCanGetHook()
	{
		$filter = Filter::create([
			Filter::HOOK => 'test',
			Filter::CALLABLE => function () {}
		]);

		$this->assertEquals('test', $filter->hook());
	}

	public function testCanGetPriority()
	{
		$filter = Filter::create([
			Filter::HOOK => 'test',
			Filter::CALLABLE => function() {},
			Filter::PRIORITY => 99
		]);

		$this->assertEquals(99, $filter->priority());
	}

	public function testCanGetNumArgs()
	{
		$filter = Filter::create([
			Filter::HOOK => 'test',
			Filter::CALLABLE => function() {},
			Filter::NUM_ARGS => 3
		]);

		$this->assertEquals(3, $filter->numArgs());
	}
}