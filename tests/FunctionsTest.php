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

	public function testErrorLog(): void
	{
		container()->get('config')->set(__DIR__ . '/Config/config.php');

		\JayWolfeLib\error_log('test', __DIR__ . '/Config/config.php');

		$this->assertFileExists(__DIR__ . '/log.txt');

		$contents = file_get_contents(__DIR__ . '/log.txt');

		$this->assertEquals($contents, 'test' . PHP_EOL);

		unlink(__DIR__ . '/log.txt');
	}

	public function testFetchArray(): void
	{
		$file = __DIR__ . '/test.php';

		file_put_contents($file, "<?php return [1, 2, 3];");

		container()->get('config')->set(__DIR__ . '/Config/config.php');

		$arr = fetch_array('test', __DIR__ . '/Config/config.php');
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