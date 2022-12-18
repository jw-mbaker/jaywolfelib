<?php

namespace JayWolfeLib\Tests\Hooks;

use JayWolfeLib\Hooks\Ajax;
use JayWolfeLib\Hooks\Handler;
use DownShift\WordPress\EventEmitter;
use DownShift\WordPress\EventEmitterInterface;
use WP_Mock;
use WP_Mock\Matcher\AnyInstance;
use Mockery;

use function JayWolfeLib\container;

class AjaxTest extends WP_Mock\Tools\TestCase
{
	private $ajaxCallback;

	public function setUp(): void
	{
		WP_Mock::setUp();

		$this->ajaxCallback = function() {};
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		container()->flush();
		Mockery::close();
	}

	public function testCanAddAjax(): void
	{
		//WP_Mock::expectActionAdded('wp_ajax_test_ajax', new AnyInstance( Handler::class ));

		$ret = Ajax::add_ajax('test_ajax', $this->ajaxCallback);

		$this->assertInstanceOf(Handler::class, $ret);
	}

	public function testCanInvokeHandler(): void
	{
		$bool = true;
		$this->assertTrue($bool);

		$handler = Ajax::add_ajax('test_ajax', function() use (&$bool) {
			$bool = false;
		});

		call_user_func($handler);

		$this->assertFalse($bool);
	}

	public function testCanDoAjax(): void
	{
		WP_Mock::expectAction('wp_ajax_test_ajax');

		$ret = Ajax::do_action('wp_ajax_test_ajax');

		$this->assertInstanceOf(EventEmitterInterface::class, $ret);
	}

	public function testHasAjax(): void
	{
		$ajax = ['wp_ajax_test_ajax'];

		WP_Mock::alias('has_action', function(string $hook) use ($ajax) {
			return in_array($hook, $ajax);
		});

		$ret = Ajax::has_ajax('test_ajax');
		$this->assertTrue($ret);

		$ret = Ajax::has_ajax('123');
		$this->assertFalse($ret);
	}
}