<?php

namespace JayWolfeLib\Tests\Views;

use JayWolfeLib\Views\ViewFactory;
use JayWolfeLib\Views\ViewInterface;
use JayWolfeLib\Component\Config\ConfigInterface;
use JayWolfeLib\Component\Config\Config;
use WP_Mock;
use Mockery;

class ViewFactoryTest extends \WP_Mock\Tools\TestCase
{
	public function setUp(): void
	{
		WP_Mock::setUp();
		WP_Mock::alias('trailingslashit', fn(string $str) => rtrim($str, '\\/') . '/');
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	public function testMakeFromAbsolutePath()
	{
		$factory = new ViewFactory();

		$str = $factory->make('mock-template.php', [], MOCK_TEMPLATE_PATH);
		$this->assertEquals('Hello World!', $str);
	}

	public function testMakeFromAbsolutePathWithoutExtension()
	{
		$factory = new ViewFactory();

		$str = $factory->make('mock-template', [], MOCK_TEMPLATE_PATH);
		$this->assertEquals('Hello World!', $str);
	}

	public function testMakeFromAbsolutePathWithArgs()
	{
		$factory = new ViewFactory();

		$str = $factory->make('mock-template', ['foo' => 'bar'], MOCK_TEMPLATE_PATH);
		$this->assertEquals('bar', $str);
	}

	public function testMakeFromConfig()
	{
		$factory = new ViewFactory();
		$config = Config::from_file(MOCK_CONFIG_FILE);

		$str = $factory->make('mock-template.php', [], null, $config);
		$this->assertEquals('Hello World!', $str);
	}

	public function testMakeFromConfigWithoutExtension()
	{
		$factory = new ViewFactory();
		$config = Config::from_file(MOCK_CONFIG_FILE);

		$str = $factory->make('mock-template', [], null, $config);
		$this->assertEquals('Hello World!', $str);
	}

	public function testMakeFromConfigWithArgs()
	{
		$factory = new ViewFactory();
		$config = Config::from_file(MOCK_CONFIG_FILE);

		$str = $factory->make('mock-template', ['foo' => 'bar'], null, $config);
		$this->assertEquals('bar', $str);
	}
}