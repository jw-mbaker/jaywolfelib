<?php

namespace JayWolfeLib\Tests\Traits;

use JayWolfeLib\Config\Config;
use JayWolfeLib\Includes\Dependencies;
use JayWolfeLib\Traits\JayWolfeTrait;
use WP_Mock;
use Mockery;

class JayWolfeTraitTest extends WP_Mock\Tools\TestCase
{
	use JayWolfeTrait;

	public function setUp(): void
	{
		WP_Mock::setUp();
		WP_Mock::passthruFunction('sanitize_key');
		WP_Mock::alias('trailingslashit', fn($str) => rtrim($str, '/\\') . '/');
		$this->setConfig();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	public function testFetchArray(): void
	{
		$file = __DIR__ . '/test.php';

		file_put_contents($file, "<?php return [1, 2, 3];");

		$arr = $this->fetch_array('test');
		unlink($file);

		$this->assertEquals($arr, [1, 2, 3]);
	}

	public function testErrorLog(): void
	{
		$this->error_log('test');

		$this->assertFileExists(__DIR__ . '/log.txt');

		$contents = file_get_contents(__DIR__ . '/log.txt');

		$this->assertEquals($contents, 'test' . PHP_EOL);

		unlink(__DIR__ . '/log.txt');
	}

	private function setConfig(): void
	{
		$this->set_config(
			new Config(
				[
					'paths' => [
						'arrays' => __DIR__,
						'log' => __DIR__
					]
				],
				Mockery::mock(Dependencies::class)
			)
		);
	}
}