<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Views;

use JayWolfeLib\Views\View;
use JayWolfeLib\Config\ConfigInterface;
use JayWolfeLib\Config\Config;
use JayWolfeLib\Exception\InvalidTemplateException;
use WP_Mock;
use Mockery;

use const JayWolfeLib\MOCK_PLUGIN_REL_PATH;
use const JayWolfeLib\MOCK_TEMPLATE_PATH;
use const JayWolfeLib\MOCK_CONFIG_FILE;

class ViewTest extends \WP_Mock\Tools\TestCase
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

	public function testRenderFromAbsolutePath()
	{
		$view = new View();
		$str = $view->render('mock-template.php', [], MOCK_TEMPLATE_PATH);

		$this->assertEquals('Hello World!', $str);
	}

	public function testRenderFromAbsolutePathWithoutExtension()
	{
		$view = new View();
		$str = $view->render('mock-template', [], MOCK_TEMPLATE_PATH);

		$this->assertEquals('Hello World!', $str);
	}

	public function testRenderFromAbsolutePathWithArgs()
	{
		$view = new View();
		$str = $view->render('mock-template', ['foo' => 'bar'], MOCK_TEMPLATE_PATH);

		$this->assertEquals('bar', $str);
	}

	public function testThrowsInvalidArgumentExceptionWithoutConfig()
	{
		$view = new View();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage(
			sprintf('$templatePath must be specified if %s is not provided.', ConfigInterface::class)
		);

		$view->render('mock-template');
	}

	public function testRenderFromConfig()
	{
		$config = $this->createConfig();
		$view = new View($config);

		$str = $view->render('mock-template.php');
		$this->assertEquals('Hello World!', $str);
	}

	public function testRenderFromConfigWithoutExtension()
	{
		$config = $this->createConfig();
		$view = new View($config);

		$str = $view->render('mock-template');
		$this->assertEquals('Hello World!', $str);
	}

	public function testRenderFromConfigWithArgs()
	{
		$config = $this->createConfig();
		$view = new View($config);

		$str = $view->render('mock-template', ['foo' => 'bar']);
		$this->assertEquals('bar', $str);
	}

	public function testThrowsInvalidTemplateIfTemplatePathNotSet()
	{
		$config = Mockery::mock(ConfigInterface::class);
		$view = new View($config);

		$config->expects()->get('paths')->andReturn([]);
		$config->expects()->get('plugin_file')->andReturn(MOCK_PLUGIN_REL_PATH);

		$this->expectException(InvalidTemplateException::class);
		$this->expectExceptionMessage(
			sprintf('Template path not set for %s.', MOCK_PLUGIN_REL_PATH)
		);

		$view->render('mock-template');
	}

	private function createConfig(): ConfigInterface
	{
		return Config::fromFile(MOCK_CONFIG_FILE);
	}
}