<?php

namespace JayWolfeLib\Tests\Component\Config;

use JayWolfeLib\Component\Config\ConfigInterface;
use JayWolfeLib\Component\Config\Config;
use JayWolfeLib\Component\Config\ConfigTrait;
use WP_Mock;
use Mockery;

class ConfigTraitTest extends \WP_Mock\Tools\TestCase
{
	use ConfigTrait;

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
	 * @covers \JayWolfeLib\Component\Config\ConfigTrait::set_config
	 * @group config
	 */
	public function testCanSetConfig()
	{
		$this->set_config($this->createMockConfig());

		$this->assertInstanceOf(ConfigInterface::class, $this->config);
	}

	/**
	 * @covers \JayWolfeLib\Component\Config\ConfigTrait::get_config
	 * @group config
	 */
	public function testCanGetConfig()
	{
		$this->config = $this->createMockConfig();
		$this->assertSame($this->config, $this->get_config());
		$this->assertInstanceOf(ConfigInterface::class, $this->get_config());
	}

	private function createMockConfig(): ConfigInterface
	{
		return Mockery::mock(ConfigInterface::class);
	}
}