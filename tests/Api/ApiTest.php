<?php

namespace JayWolfeLib\Tests\Api;

use JayWolfeLib\Api\Api;
use JayWolfeLib\Hooks\Handler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Componenet\HttpFoundation\JsonResponse;
use WP_Mock;
use Mockery;

use function JayWolfeLib\container;

class ApiTest extends WP_Mock\Tools\TestCase
{
	private $apiCallback;
	private $api;
	private $request;

	public function setUp(): void
	{
		WP_Mock::setUp();
		WP_Mock::userFunction('wp_die');

		$this->apiCallback = function() {};
		$this->request = Mockery::mock(Request::class);
		$this->api = new Api($this->request);
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		container()->flush();
		Mockery::close();
	}

	/**
	 * @doesNotPerformAssertions
	 *
	 * @return void
	 */
	public function testCanRegisterHook(): void
	{
		WP_Mock::expectActionAdded('test_api', $this->apiCallback);

		$this->api->register_hook('test_api', $this->apiCallback);
	}

	public function testCanRegisterHookStatically(): void
	{
		$res = Api::add_api('test_api', $this->apiCallback);

		$this->assertInstanceOf(Handler::class, $res);
	}

	/**
	 * @doesNotPerformAssertions
	 *
	 * @return void
	 */
	public function testCanExecuteHook(): void
	{
		$this->api->register_hook('test_api', $this->apiCallback);

		WP_Mock::expectAction('test_api');

		$this->request->expects()->getMethod()->twice()->andReturn('GET');
		$this->request->expects()->get('action')->twice()->andReturn('test_api');
		$this->request->expects()->get('key')->andReturn(null);

		$this->api->do_api();
	}

	/**
	 * @doesNotPerformAssertions
	 *
	 * @return void
	 */
	public function testExitsMethodIfApiNotInHooksArray(): void
	{
		$this->api->register_hook('tessst_api', $this->apiCallback);

		$this->request->expects()->getMethod()->andReturn('GET');
		$this->request->expects()->get('action')->twice()->andReturn('test_api');

		$this->api->do_api();
	}

	/**
	 * @doesNotPerformAssertions
	 *
	 * @return void
	 */
	public function testSendsActionNotRecognizedResponse(): void
	{
		$this->api->register_hook('test_api', $this->apiCallback, 'GET', '123');

		$this->request->expects()->getMethod()->andReturn('GET');
		$this->request->expects()->get('action')->twice()->andReturn('test_api');
		$this->request->expects()->get('key')->twice()->andReturn('1234');

		$this->api->do_api();
	}

	/**
	 * @doesNotPerformAssertions
	 *
	 * @return void
	 */
	public function testSendsInvalidMethodResponse(): void
	{
		$this->api->register_hook('test_api', $this->apiCallback, 'POST');

		$this->request->expects()->getMethod()->twice()->andReturn('GET');
		$this->request->expects()->get('action')->twice()->andReturn('test_api');
		$this->request->expects()->get('key')->andReturn(null);

		$this->api->do_api();
	}

	public function testCanGetApiInstance(): void
	{
		$api = Api::get_api_manager();
		$this->assertInstanceOf(Api::class, $api);
	}
}