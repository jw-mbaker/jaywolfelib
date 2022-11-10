<?php

namespace JayWolfeLib\Tests\Config;

use JayWolfeLib\Container;
use JayWolfeLib\Config\Factory;
use JayWolfeLib\Config\ConfigInterface;
use WP_Mock;

class FactoryTest extends WP_Mock\Tools\TestCase
{
	public function setUp(): void
	{
		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
	}

	public function testCanCreateConfigInstance(): void
	{
		WP_Mock::passthruFunction('plugin_basename');
		$factory = new Factory(new Container(), new Container());

		$config = $factory->get(__FILE__);

		$this->assertInstanceOf(ConfigInterface::class, $config);
	}

	public function testCanGetContainer(): void
	{
		$container = new Container();

		$factory = new Factory($container, new Container());

		$this->assertSame($container, $factory->get_container());
	}
}