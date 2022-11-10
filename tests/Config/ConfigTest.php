<?php

namespace JayWolfeLib\Tests\Config;

use JayWolfeLib\Config\Config;
use WP_Mock;

class ConfigTest extends WP_Mock\Tools\TestCase
{
	public function setUp(): void
	{
		WP_Mock::setUp();
		WP_Mock::passthruFunction('sanitize_key');
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
	}

	public function testCanSetConfigSetting(): void
	{
		$config = new Config(__FILE__);

		$opt = $config->set('test', 123);

		$this->assertEquals($opt, 123);
	}

	public function testCanGetConfigSetting(): void
	{
		$config = new Config(__FILE__);

		$config->set('test', [1, 2, 3]);

		$this->assertEquals($config->get('test'), [1, 2, 3]);
	}

	public function testCanDeleteConfigSetting(): void
	{
		$config = new Config(__FILE__);

		$config->set('test', 123);

		$arr = $config->get_config();
		$this->assertArrayHasKey('test', $arr);

		$config->delete('test');

		$arr = $config->get_config();
		$this->assertFalse(isset($arr['test']));
	}

	public function testCanGetConfig(): void
	{
		$config = new Config(__FILE__);

		$config->set('test', 123);

		$arr = $config->get_config();

		$this->assertEquals($arr, [
			'plugin_file' => __FILE__,
			'test' => 123
		]);
	}
}