<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Views;

use JayWolfeLib\Views\ViewFactory;
use JayWolfeLib\Views\ViewInterface;
use JayWolfeLib\Config\ConfigInterface;
use JayWolfeLib\Config\Config;
use JayWolfeLib\Exception\InvalidTemplateException;
use WP_Mock;
use Mockery;

use const JayWolfeLib\MOCK_PLUGIN_REL_PATH;
use const JayWolfeLib\MOCK_TEMPLATE_PATH;
use const JayWolfeLib\MOCK_CONFIG_FILE;

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

	public function testThrowsInvalidArgumentExceptionWithoutConfig()
	{
		$factory = new ViewFactory();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage(
			sprintf('$templatePath must be specified if %s is not provided.', ConfigInterface::class)
		);

		$factory->make('mock-template');
	}

	public function testMakeFromConfig()
	{
		$factory = new ViewFactory();
		$config = Config::fromFile(MOCK_CONFIG_FILE);

		$str = $factory->make('mock-template.php', [], null, $config);
		$this->assertEquals('Hello World!', $str);
	}

	public function testMakeFromConfigWithoutExtension()
	{
		$factory = new ViewFactory();
		$config = Config::fromFile(MOCK_CONFIG_FILE);

		$str = $factory->make('mock-template', [], null, $config);
		$this->assertEquals('Hello World!', $str);
	}

	public function testMakeFromConfigWithArgs()
	{
		$factory = new ViewFactory();
		$config = Config::fromFile(MOCK_CONFIG_FILE);

		$str = $factory->make('mock-template', ['foo' => 'bar'], null, $config);
		$this->assertEquals('bar', $str);
	}

	public function testThrowsInvalidTemplateIfTemplatePathNotSet()
	{
		$config = Mockery::mock(ConfigInterface::class);
		$factory = new ViewFactory();

		$config->expects()->get('paths')->andReturn([]);
		$config->expects()->get('plugin_file')->andReturn(MOCK_PLUGIN_REL_PATH);

		$this->expectException(InvalidTemplateException::class);
		$this->expectExceptionMessage(
			sprintf('Template path not set for %s.', MOCK_PLUGIN_REL_PATH)
		);

		$factory->make('mock-template', [], null, $config);
	}
}