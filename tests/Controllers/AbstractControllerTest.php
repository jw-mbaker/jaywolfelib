<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Controllers;

use JayWolfeLib\Controllers\AbstractController;
use JayWolfeLib\Config\ConfigInterface;
use JayWolfeLib\Views\ViewInterface;
use JayWolfeLib\Views\ViewFactory;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\File;
use WP_Mock;
use Mockery;

class AbstractControllerTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
	}

	public function tearDown(): void
	{
		Mockery::close();
	}

	public function testRenderView()
	{
		$factory = Mockery::mock(ViewFactory::class);
		$config = Mockery::mock(ConfigInterface::class);

		$factory->expects()->make('mock-template', [], null, $config)->andReturn('foo');

		$this->container->set(ViewFactory::class, $factory);

		$controller = $this->createController();
		$controller->setContainer($this->container);
		$controller->setConfig($config);

		$this->assertEquals('foo', $controller->renderView('mock-template'));
	}

	public function testRender()
	{
		$factory = Mockery::mock(ViewFactory::class);
		$config = Mockery::mock(ConfigInterface::class);

		$factory->expects()->make('mock-template', [], null, $config)->andReturn('foo');

		$this->container->set(ViewFactory::class, $factory);

		$controller = $this->createController();
		$controller->setContainer($this->container);
		$controller->setConfig($config);

		$this->assertEquals('foo', $controller->render('mock-template')->getContent());
	}

	public function testJson()
	{
		$controller = $this->createController();
		$controller->setContainer($this->container);

		$response = $controller->json([]);

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals('[]', $response->getContent());
	}

	public function testFile()
	{
		$controller = $this->createController();
		$controller->setContainer($this->container);

		$response = $controller->file(new File(__FILE__));
		$this->assertInstanceOf(BinaryFileResponse::class, $response);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertStringContainsString(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $response->headers->get('content-disposition'));
		$this->assertStringContainsString(basename(__FILE__), $response->headers->get('content-disposition'));
	}

	public function testFileWithOwnFileName()
	{
		$controller = $this->createController();

		$fileName = 'test.php';
		$response = $controller->file(new File(__FILE__), $fileName);

		$this->assertInstanceOf(BinaryFileResponse::class, $response);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertStringContainsString(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $response->headers->get('content-disposition'));
		$this->assertStringContainsString($fileName, $response->headers->get('content-disposition'));
	}

	public function testFileFromPath()
	{
		$controller = $this->createController();

		$response = $controller->file(__FILE__);

		$this->assertInstanceOf(BinaryFileResponse::class, $response);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertStringContainsString(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $response->headers->get('content-disposition'));
		$this->assertStringContainsString(basename(__FILE__), $response->headers->get('content-disposition'));
	}

	private function createController(): MockController
	{
		return new MockController();
	}
}