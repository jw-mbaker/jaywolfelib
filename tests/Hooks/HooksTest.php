<?php

namespace JayWolfeLib\Tests\Hooks;

use JayWolfeLib\Hooks\Hooks;
use DownShift\WordPress\EventEmitterInterface;
use WP_Mock;

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

	public function testCanDoAction(): void
	{
		WP_Mock::expectAction('test_action', 'test');
		$ret = Hooks::do_action('test_action', 'test');

		$this->assertInstanceOf(EventEmitterInterface::class, $ret);
	}

	public function testCanApplyFilters(): void
	{
		WP_Mock::expectFilter('test_filter', 'test');
		$ret = Hooks::apply_filters('test_filter', 'test');

		$this->assertEquals($ret, 'test');
	}
}