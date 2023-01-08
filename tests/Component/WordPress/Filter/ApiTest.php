<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Component\WordPress\Filter;

use JayWolfeLib\Component\WordPress\Filter\HookInterface;
use JayWolfeLib\Component\WordPress\Filter\Api;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use WP_Mock;
use Mockery;

class ApiTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private Request $request;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
		$this->request = Mockery::mock(Request::class);
		WP_Mock::setUp();
		WP_Mock::userFunction('wp_die');
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group hook
	 * @group api
	 * @group action
	 * @group wordpress
	 */
	public function testCanInvokeApi()
	{
		$response = Mockery::mock(Response::class);

		$api = Api::create([
			Api::HOOK => 'test',
			Api::CALLABLE => function(Request $request) use ($response) {
				$this->assertInstanceOf(Request::class, $request);
				return $response;
			},
			Api::REQUEST => $this->request
		]);

		$this->request->expects()->getMethod()->twice()->andReturn('GET');
		$this->request->expects()->get('key')->twice()->andReturn(null);

		WP_Mock::onAction($api->hook())
			->with(null)
			->perform(function() use ($api) {
				$response = $this->container->call($api);
				$this->assertInstanceOf(Response::class, $response);
			});

		do_action($api->hook());
	}

	/**
	 * @group hook
	 * @group api
	 * @group action
	 * @group wordpress
	 */
	public function testReturnsJsonResponseOnInvalidApiKey()
	{
		$api = Api::create([
			Api::HOOK => 'test',
			Api::CALLABLE => function(Request $request) {

			},
			Api::REQUEST => $this->request,
			Api::API_KEY => 'good_key'
		]);

		$this->request->expects()->getMethod()->andReturn('GET');
		$this->request->expects()->get('key')->twice()->andReturn('bad_key');

		$response = $this->container->call($api);

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals($response->getContent(), json_encode(Api::ACTION_NOT_RECOGNIZED));
		$this->assertEquals($response->getStatusCode(), Response::HTTP_NOT_FOUND);
	}

	/**
	 * @group hook
	 * @group api
	 * @group action
	 * @group wordpress
	 */
	public function testReturnsJsonResponseOnInvalidMethod()
	{
		$api = Api::create([
			Api::HOOK => 'test',
			Api::CALLABLE => function(Request $request) {

			},
			Api::REQUEST => $this->request
		]);

		$this->request->expects()->getMethod()->andReturn('POST');
		$this->request->expects()->get('key')->twice()->andReturn(null);

		$response = $this->container->call($api);

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals($response->getContent(), json_encode(Api::INVALID_METHOD));
		$this->assertEquals($response->getStatusCode(), Response::HTTP_NOT_FOUND);
	}
}