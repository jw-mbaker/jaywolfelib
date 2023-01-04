<?php

namespace JayWolfeLib\Tests\Component\WordPress\Filter;

use JayWolfeLib\Component\WordPress\Filter\FilterCollection;
use JayWolfeLib\Component\WordPress\Filter\HookInterface;
use JayWolfeLib\Component\WordPress\Filter\Filter;
use JayWolfeLib\Component\WordPress\Filter\Action;
use JayWolfeLib\Component\WordPress\Filter\Api;
use JayWolfeLib\Tests\Component\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use WP_Mock;
use WP_Mock\InvokedFilterValue;
use Mockery;

class FilterCollectionTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private $collection;
	private $request;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
		$this->collection = new FilterCollection($this->container);
		$this->request = Mockery::mock(Request::class);
		WP_Mock::setUp();
		WP_Mock::userFunction('remove_filter');
		WP_Mock::userFunction('remove_action');
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 */
	public function testCanAddHook()
	{
		$hook = Mockery::mock(HookInterface::class);
		$hook->expects()->id()->twice()->andReturn(spl_object_hash($hook));
		$hook->expects()->hook()->andReturn('test');
		$hook->expects()->get('priority')->andReturn(10);
		$hook->expects()->get('num_args')->andReturn(1);
		
		$this->collection->add_filter($hook);
		$this->assertContains($hook, $this->collection->all());
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 */
	public function testCanRemoveHook()
	{
		$callable = function() {};

		$hook = Mockery::mock(HookInterface::class);
		$hook->expects()->id()->times(3)->andReturn(spl_object_hash($hook));
		$hook->expects()->hook()->times(3)->andReturn('test');
		$hook->expects()->get('priority')->twice()->andReturn(10);
		$hook->expects()->get('num_args')->andReturn(1);
		$hook->expects()->get('callable')->andReturn($callable);

		$this->collection->add_filter($hook);
		$this->assertContains($hook, $this->collection->all());

		$bool = $this->collection->remove_filter('test', $callable);
		$this->assertTrue($bool);

		$this->assertNotContains($hook, $this->collection->all());
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 */
	public function testRemoveFilterReturnsFalseOnInvalidKey()
	{
		$this->assertArrayNotHasKey('test', $this->collection->all());
		$bool = $this->collection->remove_filter('test', function() {});
		$this->assertFalse($bool);
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 */
	public function testCanGetHook()
	{
		$hook = Mockery::mock(HookInterface::class);
		$hook->expects()->id()->twice()->andReturn(spl_object_hash($hook));
		$hook->expects()->hook()->andReturn('test');
		$hook->expects()->get('priority')->andReturn(10);
		$hook->expects()->get('num_args')->andReturn(1);
		
		$this->collection->add_filter($hook);
		$this->assertSame($hook, $this->collection->get(spl_object_hash($hook)));
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 * @group filter
	 */
	public function testCanInvokeFilter()
	{
		$filter = new Filter('test', function(string $test) {
			return $test;
		});

		WP_Mock::expectFilterAdded('test', [$this->collection, $filter->id()]);
		$this->collection->add_filter($filter);

		$this->assertSame($filter, $this->collection->get($filter->id()));

		WP_Mock::onFilter('test')
			->with('test')
			->reply(new InvokedFilterValue(function() use ($filter) {
				return call_user_func([$this->collection, $filter->id()], 'test');
			}));

		$val = apply_filters('test', 'test');
		$this->assertEquals('test', $val);
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 * @group action
	 */
	public function testCanInvokeAction()
	{
		$action = new Action('test', function() {
			$this->assertTrue(true);
		});

		WP_Mock::expectFilterAdded('test', [$this->collection, $action->id()]);
		$this->collection->add_action($action);

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($action) {
				call_user_func([$this->collection, $action->id()]);
			});

		do_action('test');
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 * @group api
	 * @group action
	 */
	public function testCanInvokeApi()
	{
		$api = new Api('test', function(Request $request) {
			$this->assertTrue(true);
		}, 'GET', $this->request);

		$this->request->expects()->getMethod()->twice()->andReturn('GET');
		$this->request->expects()->get('key')->twice()->andReturn(null);

		WP_Mock::expectFilterAdded('test', [$this->collection, $api->id()]);
		$this->collection->add_action($api);

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($api) {
				call_user_func([$this->collection, $api->id()]);
			});

		do_action('test');
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 * @group filter
	 */
	public function testCanInvokeFilterWithTypeHint()
	{
		$filter = new Filter('test', function(MockTypeHint $th, string $test) {
			$this->assertInstanceOf(MockTypeHint::class, $th);
			return $test;
		}, ['map' => [\DI\get(MockTypeHint::class)]]);

		$this->collection->add_filter($filter);

		$this->assertSame($filter, $this->collection->get($filter->id()));

		WP_Mock::onFilter('test')
			->with('test')
			->reply(new InvokedFilterValue(function() use ($filter) {
				return call_user_func([$this->collection, $filter->id()], 'test');
			}));

		$val = apply_filters('test', 'test');
		$this->assertEquals('test', $val);
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 * @group action
	 */
	public function testCanInvokeActionWithTypeHint()
	{
		$action = new Action('test', function(MockTypeHint $th) {
			$this->assertInstanceOf(MockTypeHint::class, $th);
		});

		$this->collection->add_action($action);

		$this->assertSame($action, $this->collection->get($action->id()));

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($action) {
				call_user_func([$this->collection, $action->id()]);
			});

		do_action('test');
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 * @group api
	 * @group action
	 */
	public function testCanInvokeApiWithTypeHint()
	{
		$api = new Api('test', function(Request $request, MockTypeHint $th) {
			$this->assertInstanceOf(MockTypeHint::class, $th);
		}, 'GET', $this->request);

		$this->request->expects()->getMethod()->twice()->andReturn('GET');
		$this->request->expects()->get('key')->twice()->andReturn(null);

		$this->collection->add_action($api);

		$this->assertSame($api, $this->collection->get($api->id()));

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($api) {
				call_user_func([$this->collection, $api->id()]);
			});

		do_action('test');
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 * @group action
	 */
	public function testCanInvokeActionWithTypeHintAndArguments()
	{
		$action = new Action('test', function(MockTypeHint $th, bool $bool, int $int) {
			$this->assertTrue($bool);
			$this->assertEquals(123, $int);
		}, ['map' => [\DI\get(MockTypeHint::class)], 'num_args' => 2]);

		$this->collection->add_action($action);

		$this->assertSame($action, $this->collection->get($action->id()));

		WP_Mock::onAction('test')
			->with(true, 123)
			->perform(function() use ($action) {
				call_user_func([$this->collection, $action->id()], true, 123);
			});

		do_action('test', true, 123);
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 * @group action
	 */
	public function testActionCanTakeRequestObjectAndHandleResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$this->container->set(Request::class, Mockery::mock(Request::class));

		$action = new Action('test', function(Request $request) use ($response) {
			$this->assertInstanceOf(Request::class, $request);
			return $response;
		});

		$this->collection->add_action($action);

		$this->assertSame($action, $this->collection->get($action->id()));

		$response->expects()->send();

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($action) {
				call_user_func([$this->collection, $action->id()]);
			});

		do_action('test');
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 * @group api
	 * @group action
	 */
	public function testApiReturnsResponse()
	{
		$response = Mockery::mock(Response::class);

		$api = new Api('test', function(Request $request) use ($response) {
			return $response;
		}, 'GET', $this->request);

		$this->request->expects()->getMethod()->twice()->andReturn('GET');
		$this->request->expects()->get('key')->twice()->andReturn(null);

		$this->collection->add_action($api);

		$this->assertSame($api, $this->collection->get($api->id()));

		$response->expects()->send();

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($api) {
				call_user_func([$this->collection, $api->id()]);
			});

		do_action('test');
	}

	/**
	 * @group hook
	 * @group collection
	 * @group wordpress
	 * @group api
	 * @group action
	 *
	 * @return void
	 */
	public function testApiReturnsJsonResponseOnInvalidApiKey()
	{
		$api = new Api(
			'test',
			function(Request $request) {
			
			},
			'GET',
			$this->request,
			[
				'api_key' => 'good_key'
			]
		);

		$this->request->expects()->getMethod()->andReturn('GET');
		$this->request->expects()->get('key')->twice()->andReturn('bad_key');

		$this->collection->add_action($api);

		$this->assertSame($api, $this->collection->get($api->id()));

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($api) {
				$response = call_user_func([$this->collection, $api->id()]);

				$this->assertInstanceOf(JsonResponse::class, $response);
				$this->assertEquals($response->getContent(), json_encode(Api::ACTION_NOT_RECOGNIZED));
				$this->assertEquals($response->getStatusCode(), Response::HTTP_NOT_FOUND);
			});

		do_action('test');
	}

	/**
	 * @group hook
	 * @group collection
	 * @group wordpress
	 * @group api
	 * @group action
	 */
	public function testApiReturnsJsonResponseOnInvalidMethod()
	{
		$api = new Api(
			'test',
			function(Request $request) {
			
			},
			'GET',
			$this->request
		);

		$this->request->expects()->getMethod()->twice()->andReturn('POST');
		$this->request->expects()->get('key')->twice()->andReturn(null);

		$this->collection->add_action($api);

		$this->assertSame($api, $this->collection->get($api->id()));

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($api) {
				$response = call_user_func([$this->collection, $api->id()]);

				$this->assertInstanceOf(JsonResponse::class, $response);
				$this->assertEquals($response->getContent(), json_encode(Api::INVALID_METHOD));
				$this->assertEquals($response->getStatusCode(), Response::HTTP_NOT_FOUND);
			});

		do_action('test');
	}
}