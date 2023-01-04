<?php

namespace JayWolfeLib\Tests\Component\WordPress\Filter;

use JayWolfeLib\Component\WordPress\Filter\Filter;
use JayWolfeLib\Tests\Component\MockTypeHint;
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
		$filter = new Filter('test', function(string $test) {
			$this->assertEquals($test, 'test');
		});

		WP_Mock::onFilter($filter->hook())
			->with('test')
			->reply(new InvokedFilterValue(function($test) use ($filter) {
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
		$filter = new Filter('test', function(MockTypeHint $th, string $test) {
			$this->assertEquals($test, 'test');
		}, ['map' => [\DI\get(MockTypeHint::class)]]);

		WP_Mock::onFilter($filter->hook())
			->with('test')
			->reply(new InvokedFilterValue(function(string $test) use ($filter) {
				return $this->container->call($filter, [$this->container, ...$filter->get('map'), $test]);
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
		$filter = new Filter('test', function() {});
		$this->assertNotNull($filter->id());
	}

	/**
	 * @group hook
	 * @group filter
	 * @group wordpress
	 */
	public function testCanGetHook()
	{
		$filter = new Filter('test', function() {});
		$this->assertEquals($filter->hook(), 'test');
	}
}