<?php

namespace JayWolfeLib\Tests\Component\Config;

use JayWolfeLib\Component\Config\ConfigInterface;
use JayWolfeLib\Component\Config\Config;
use JayWolfeLib\Component\Config\Dependencies;
use WP_Mock;
use Mockery;

class ConfigTest extends \WP_Mock\Tools\TestCase
{
	public function setUp(): void
	{
		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group config
	 * @depends testCanGetValue
	 */
	public function testCanSetValue()
	{
		$dependencies = $this->createMockDependencies();
		$config = new Config([], $dependencies);

		$config->set('test', 'test123');
		$this->assertEquals($config->get('test'), 'test123');
	}

	/**
	 * @group config
	 */
	public function testCanGetValue()
	{
		$dependencies = $this->createMockDependencies();
		$config = new Config(['test' => 'test123'], $dependencies);

		$this->assertEquals($config->get('test'), 'test123');
	}

	/**
	 * @group config
	 */
	public function testCanRemoveValue()
	{
		$dependencies = $this->createMockDependencies();
		$config = new Config(['test' => 'test123'], $dependencies);

		$this->assertEquals($config->get('test'), 'test123');
		$config->remove('test');
		$this->assertNull($config->get('test'));
	}

	/**
	 * @covers \JayWolfeLib\Component\Config\Config::create
	 * @group config
	 */
	public function testCreateConfigStatically()
	{
		$dependencies = $this->createMockDependencies();

		$config = Config::create(MOCK_CONFIG_FILE, $dependencies);

		$this->assertInstanceOf(ConfigInterface::class, $config);
		$this->assertInstanceOf(Config::class, $config);
	}

	/**
	 * @covers \JayWolfeLib\Component\Config\Config::create
	 * @group config
	 */
	public function testCreateConfigStaticallyThrowsInvalidConfig()
	{
		$dependencies = $this->createMockDependencies();

		$file = 'xyz.file';

		$this->expectException(\JayWolfeLib\Exception\InvalidConfig::class);
		$this->expectExceptionMessage(sprintf('%s not found.', $file));

		$config = Config::create($file, $dependencies);
	}

	private function createMockDependencies(): Dependencies
	{
		return Mockery::mock(Dependencies::class);
	}
}