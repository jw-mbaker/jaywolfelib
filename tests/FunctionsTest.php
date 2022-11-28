<?php

namespace JayWolfeLib\Tests;

use JayWolfeLib\Container;
use JayWolfeLib\Hooks\Hooks;
use WP_Mock;

use function JayWolfeLib\validate_bool;
use function JayWolfeLib\container;

class FunctionsTest extends WP_Mock\Tools\TestCase
{
	public function setUp(): void
	{
		WP_Mock::setUp();
		WP_Mock::passthruFunction('plugin_basename');
		WP_Mock::passthruFunction('sanitize_key');
		WP_Mock::alias('trailingslashit', function($str) {
			return rtrim($str, '/\\') . '/';
		});
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		container()->flush();
	}

	public function testValidateBool(): void
	{
		$this->assertTrue(validate_bool('true'));
		$this->assertFalse(validate_bool('false'));
		$this->assertTrue(validate_bool('yes'));
		$this->assertFalse(validate_bool('no'));
	}

	public function testCanRetrieveContainer(): void
	{
		$container = container();

		$this->assertSame($container, container());
	}
}