<?php

namespace JayWolfeLib\Tests\Hooks;

use JayWolfeLib\Container;
use JayWolfeLib\Hooks\Hooks;
use DownShift\WordPress\EventEmitterInterface;
use WP_Mock;

use function JayWolfeLib\container;

class HooksTest extends WP_Mock\Tools\TestCase
{
	private $actionCallback;
	private $filterCallback;

	public function setUp(): void
	{
		WP_Mock::setUp();

		$this->actionCallback = function($arg) {};
		$this->filterCallback = function($arg) {
			return $arg;
		};
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		container()->flush();
	}

	public function testCanAddAction(): void
	{
		WP_Mock::expectActionAdded('test_action', $this->actionCallback);
		$ret = Hooks::add_action('test_action', $this->actionCallback);

		$this->assertInstanceOf(EventEmitterInterface::class, $ret);
	}

	public function testCanAddFilter(): void
	{
		WP_Mock::expectFilterAdded('test_filter', $this->filterCallback);
		$ret = Hooks::add_filter('test_filter', $this->filterCallback);

		$this->assertInstanceOf(EventEmitterInterface::class, $ret);
	}

	/**
	 * @depends testCanAddAction
	 *
	 * @return void
	 */
	public function testCanDoAction(): void
	{
		WP_Mock::expectAction('test_action', 'test');
		$ret = Hooks::do_action('test_action', 'test');

		$this->assertInstanceOf(EventEmitterInterface::class, $ret);
	}

	/**
	 * @depends testCanAddFilter
	 *
	 * @return void
	 */
	public function testCanApplyFilters(): void
	{
		WP_Mock::expectFilter('test_filter', 'test');
		$ret = Hooks::apply_filters('test_filter', 'test');

		$this->assertEquals($ret, 'test');
	}

	/**
	 * @depends testCanAddAction
	 *
	 * @return void
	 */
	public function testHasAction(): void
	{
		$actions = ['test_action'];

		WP_Mock::alias('has_action', function(string $hook) use ($actions) {
			return in_array($hook, $actions);
		});

		$ret = Hooks::has_action('test_action');
		$this->assertTrue($ret);

		$ret = Hooks::has_action('123');
		$this->assertFalse($ret);
	}

	/**
	 * @depends testCanAddFilter
	 *
	 * @return void
	 */
	public function testHasFilter(): void
	{
		$filters = ['test_filter'];

		WP_Mock::alias('has_filter', function(string $hook) use ($filters) {
			return in_array($hook, $filters);
		});

		$ret = Hooks::has_filter('test_filter');
		$this->assertTrue($ret);

		$ret = Hooks::has_filter('123');
		$this->assertFalse($ret);
	}
}