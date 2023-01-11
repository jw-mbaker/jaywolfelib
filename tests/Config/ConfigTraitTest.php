<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Config;

use JayWolfeLib\Config\ConfigInterface;
use JayWolfeLib\Config\Config;
use JayWolfeLib\Config\ConfigTrait;
use JayWolfeLib\Tests\Traits\MockConfigTrait;
use WP_Mock;
use Mockery;

class ConfigTraitTest extends \WP_Mock\Tools\TestCase
{
	use ConfigTrait;
	use MockConfigTrait;

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
	 * @covers \JayWolfeLib\Config\ConfigTrait::setConfig
	 * @group config
	 */
	public function testCanSetConfig()
	{
		$this->setConfig($this->createMockConfig());

		$this->assertInstanceOf(ConfigInterface::class, $this->config);
	}

	/**
	 * @covers \JayWolfeLib\Config\ConfigTrait::getConfig
	 * @group config
	 */
	public function testCanGetConfig()
	{
		$this->config = $this->createMockConfig();
		$this->assertSame($this->config, $this->getConfig());
		$this->assertInstanceOf(ConfigInterface::class, $this->getConfig());
	}
}