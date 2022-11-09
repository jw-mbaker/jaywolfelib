<?php

namespace JayWolfeLib\Tests\Hooks;

use JayWolfeLib\Hooks\Ajax;
use DownShift\WordPress\EventEmitter;
use DownShift\WordPress\EventEmitterInterface;
use WP_Mock;

class AjaxTest extends WP_Mock\Tools\TestCase
{
	private $ajaxCallback;
	private $container;

	public function setUp(): void
	{
		WP_Mock::setUp();

		$this->ajaxCallback = function() {};
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
	}

	public function testCanAddAjax(): void
	{
		WP_Mock::expectActionAdded('wp_ajax_test_ajax', $this->ajaxCallback);

		$ret = Ajax::add_ajax('test_ajax', $this->ajaxCallback);

		$this->assertInstanceOf(EventEmitterInterface::class, $ret);
	}

	public function testCanDoAjax(): void
	{
		WP_Mock::expectAction('wp_ajax_test_ajax');

		$ret = Ajax::do_action('wp_ajax_test_ajax');

		$this->assertInstanceOf(EventEmitterInterface::class, $ret);
	}
}