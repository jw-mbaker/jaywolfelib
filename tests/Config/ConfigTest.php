<?php

namespace JayWolfeLib\Tests\Config;

use JayWolfeLib\Config\Config;
use JayWolfeLib\Includes\Dependencies;
use WP_Mock;
use Mockery;

class ConfigTest extends WP_Mock\Tools\TestCase
{
	private $dependencies;

	public function setUp(): void
	{
		WP_Mock::setUp();
		WP_Mock::passthruFunction('sanitize_key');
		$this->dependencies = Mockery::mock(Dependencies::class);
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @dataProvider getConfig
	 *
	 * @return void
	 */
	public function testCanSetConfigSetting(array $cfg): void
	{
		$config = new Config($cfg, $this->dependencies);

		$opt = $config->set('test', 123);

		$this->assertEquals($opt, 123);
	}

	/**
	 * @dataProvider getConfig
	 *
	 * @return void
	 */
	public function testCanGetConfigSetting(array $cfg): void
	{
		$config = new Config($cfg, $this->dependencies);

		$this->assertEquals($config->get('test'), [1, 2, 3]);
	}

	/**
	 * @dataProvider getConfig
	 *
	 * @return void
	 */
	public function testCanDeleteConfigSetting(array $cfg): void
	{
		$config = new Config($cfg, $this->dependencies);

		$arr = $config->get_config();
		$this->assertArrayHasKey('test', $arr);

		$config->delete('test');

		$arr = $config->get_config();
		$this->assertFalse(isset($arr['test']));
	}

	/**
	 * @dataProvider getConfig
	 *
	 * @return void
	 */
	public function testCanGetConfig(array $cfg): void
	{
		$config = new Config($cfg, $this->dependencies);

		$arr = $config->get_config();

		$this->assertArrayHasKey('plugin_file', $arr);
		$this->assertArrayHasKey('paths', $arr);
	}

	public function getConfig(): array
	{
		return [
			'config' => [
				include __DIR__ . '/config.php'
			]
		];
	}
}