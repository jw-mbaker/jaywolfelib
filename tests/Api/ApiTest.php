<?php

namespace JayWolfeLib\Tests\Api;

use JayWolfeLib\Api\Api;
use JayWolfeLib\Hooks\Handler;
use JayWolfeLib\Input;
use WP_Mock;
use Mockery;

use function JayWolfeLib\container;

class ApiTest extends WP_Mock\Tools\TestCase
{
	private $apiCallback;
	private $api;
	private $input;

	public function setUp(): void
	{
		WP_Mock::setUp();
		WP_Mock::userFunction('wp_die');

		$this->apiCallback = function() {};
		$this->input = Mockery::mock(Input::class);
		$this->api = new Api($this->input);
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

		$this->input->expects()->server('REQUEST_METHOD')->twice()->andReturn('GET');
		$this->input->expects()->request('action')->twice()->andReturn('test_api');
		$this->input->expects()->request('key')->andReturn(null);

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

		$this->input->expects()->server('REQUEST_METHOD')->andReturn('GET');
		$this->input->expects()->request('action')->twice()->andReturn('test_api');

		$this->api->do_api();
	}

	/**
	 * @doesNotPerformAssertions
	 *
	 * @return void
	 */
	public function testInputSendsActionNotRecognizedResponse(): void
	{
		$this->api->register_hook('test_api', $this->apiCallback, 'GET', '123');

		$this->input->expects()->server('REQUEST_METHOD')->andReturn('GET');
		$this->input->expects()->request('action')->twice()->andReturn('test_api');
		$this->input->expects()->request('key')->twice()->andReturn('1234');
		$this->input->expects()->send_json(Api::ACTION_NOT_RECOGNIZED);

		$this->api->do_api();
	}

	/**
	 * @doesNotPerformAssertions
	 *
	 * @return void
	 */
	public function testInputSendsInvalidMethodResponse(): void
	{
		$this->api->register_hook('test_api', $this->apiCallback, 'POST');

		$this->input->expects()->server('REQUEST_METHOD')->twice()->andReturn('GET');
		$this->input->expects()->request('action')->twice()->andReturn('test_api');
		$this->input->expects()->request('key')->andReturn(null);
		$this->input->expects()->send_json(Api::INVALID_METHOD);

		$this->api->do_api();
	}

	public function testCanGetApiInstance(): void
	{
		$api = Api::get_api_manager();
		$this->assertInstanceOf(Api::class, $api);
	}
}