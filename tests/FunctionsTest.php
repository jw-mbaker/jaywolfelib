<?php

namespace JayWolfeLib\Tests;

use JayWolfeLib\Container;
use JayWolfeLib\Hooks\Hooks;
use WP_Mock;

use function JayWolfeLib\fetch_array;
use function JayWolfeLib\validate_bool;
use function JayWolfeLib\container;

class FunctionsTest extends WP_Mock\Tools\TestCase
{
	public function setUp(): void
	{
		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		container()->flush();
	}

	public function testFetchArray(): void
	{
		WP_Mock::passthruFunction('plugin_basename');
		WP_Mock::alias('trailingslashit', function($str) {
			return rtrim($str, '/\\') . '/';
		});

		$file = __DIR__ . '/test.php';

		file_put_contents($file, "<?php return [1, 2, 3];");

		container()->get('config')->get(__FILE__)->set('array_path', __DIR__);

		$arr = fetch_array('test', __FILE__);
		unlink($file);

		$this->assertEquals($arr, [1, 2, 3]);
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