<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\WordPress\Filter;

use JayWolfeLib\WordPress\Filter\FilterCollection;
use JayWolfeLib\WordPress\Filter\HookInterface;
use JayWolfeLib\WordPress\Filter\Filter;
use JayWolfeLib\WordPress\Filter\Action;
use JayWolfeLib\WordPress\Filter\Api;
use JayWolfeLib\WordPress\Filter\HookId;
use JayWolfeLib\Tests\Invoker\MockTypeHint;
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

	private FilterCollection $collection;
	private Request $request;

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
		$callable = function() {};

		$filter = Filter::create([
			Filter::HOOK => 'test',
			Filter::CALLABLE => $callable
		]);
		
		$this->collection->addFilter($filter);
		$this->assertContains($filter, $this->collection->all());
		$this->assertSame($filter, $this->collection->get('test', $callable)[(string) $filter->id()]);
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 * @depends testCanGetByHook
	 */
	public function testCanRemoveHook()
	{
		$callable = function() {};

		$filter = Filter::create([
			Filter::HOOK => 'test',
			Filter::CALLABLE => $callable
		]);

		$this->collection->addFilter($filter);
		$this->assertContains($filter, $this->collection->all());

		$bool = $this->collection->removeFilter('test', $callable);
		$this->assertTrue($bool);

		$this->assertNotContains($filter, $this->collection->all());
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 * @depends testCanGetByHook
	 */
	public function testRemoveFilterReturnsFalseOnInvalidKey()
	{
		$bool = $this->collection->removeFilter('test', function() {});
		$this->assertFalse($bool);
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 */
	public function testCanGetByHook()
	{
		$callable = function() {};

		$filter = Filter::create([
			Filter::HOOK => 'test',
			Filter::CALLABLE => $callable
		]);
		
		$this->collection->addFilter($filter);
		$this->assertContains($filter, $this->collection->all());

		$objs = $this->collection->get('test', $callable);
		$this->assertContains($filter, $objs);
		$this->assertSame($filter, $objs[(string) $filter->id()]);
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 */
	public function testGetByHookReturnsEmptyArrayIfNotFound()
	{
		$hooks = $this->collection->get('test', function() {});
		$this->assertEmpty($hooks);
	}

	/**
	 * @group hook
	 * @group wordpress
	 * @group collection
	 * @group filter
	 */
	public function testCanInvokeFilter()
	{
		$filter = Filter::create([
			Filter::HOOK => 'test',
			Filter::CALLABLE => function(string $test) {
				return $test;
			}
		]);

		WP_Mock::expectFilterAdded('test', [$this->collection, (string) $filter->id()]);
		$this->collection->addFilter($filter);

		$this->assertSame($filter, $this->collection->getById($filter->id()));

		WP_Mock::onFilter('test')
			->with('test')
			->reply(new InvokedFilterValue(function() use ($filter) {
				return call_user_func([$this->collection, (string) $filter->id()], 'test');
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
		$action = Action::create([
			Action::HOOK => 'test',
			Action::CALLABLE => function() {
				$this->assertTrue(true);
			}
		]);

		WP_Mock::expectFilterAdded('test', [$this->collection, (string) $action->id()]);
		$this->collection->addAction($action);

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($action) {
				call_user_func([$this->collection, (string) $action->id()]);
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
		$api = Api::create([
			Api::HOOK => 'test',
			Api::CALLABLE => function(Request $request) {
				$this->assertTrue(true);
			},
			Api::REQUEST => $this->request
		]);

		$this->request->expects()->getMethod()->twice()->andReturn('GET');
		$this->request->expects()->get('key')->twice()->andReturn(null);

		WP_Mock::expectFilterAdded('test', [$this->collection, (string) $api->id()]);
		$this->collection->addAction($api);

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($api) {
				call_user_func([$this->collection, (string) $api->id()]);
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
		$filter = Filter::create([
			Filter::HOOK => 'test',
			Filter::CALLABLE => function(MockTypeHint $th, string $test) {
				$this->assertInstanceOf(MockTypeHint::class, $th);
				return $test;
			},
			Filter::MAP => [\DI\get(MockTypeHint::class)]
		]);

		$this->collection->addFilter($filter);

		$this->assertSame($filter, $this->collection->getById($filter->id()));

		WP_Mock::onFilter('test')
			->with('test')
			->reply(new InvokedFilterValue(function() use ($filter) {
				return call_user_func([$this->collection, (string) $filter->id()], 'test');
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
		$action =  Action::create([
			Action::HOOK => 'test',
			Action::CALLABLE => function(MockTypeHint $th) {
				$this->assertInstanceOf(MockTypeHint::class, $th);
			}
		]);

		$this->collection->addAction($action);

		$this->assertSame($action, $this->collection->getById($action->id()));

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($action) {
				call_user_func([$this->collection, (string) $action->id()]);
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
		$api = Api::create([
			Api::HOOK => 'test',
			Api::CALLABLE => function(Request $request, MockTypeHint $th) {
				$this->assertInstanceOf(MockTypeHint::class, $th);
			},
			Api::REQUEST => $this->request
		]);

		$this->request->expects()->getMethod()->twice()->andReturn('GET');
		$this->request->expects()->get('key')->twice()->andReturn(null);

		$this->collection->addAction($api);

		$this->assertSame($api, $this->collection->getById($api->id()));

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($api) {
				call_user_func([$this->collection, (string) $api->id()]);
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
		$action = Action::create([
			Action::HOOK => 'test',
			Action::CALLABLE => function(MockTypeHint $th, bool $bool, int $int) {
				$this->assertTrue($bool);
				$this->assertEquals(123, $int);
			},
			Action::MAP => [\DI\get(MockTypeHint::class)],
			Action::NUM_ARGS => 2
		]);

		$this->collection->addAction($action);

		$this->assertSame($action, $this->collection->getById($action->id()));

		WP_Mock::onAction('test')
			->with(true, 123)
			->perform(function() use ($action) {
				call_user_func([$this->collection, (string) $action->id()], true, 123);
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

		$action = Action::create([
			Action::HOOK => 'test',
			Action::CALLABLE => function(Request $request) use ($response) {
				$this->assertInstanceOf(Request::class, $request);
				return $response;
			}
		]);

		$this->collection->addAction($action);

		$this->assertSame($action, $this->collection->getById($action->id()));

		$response->expects()->send();

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($action) {
				call_user_func([$this->collection, (string) $action->id()]);
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

		$api = Api::create([
			Api::HOOK => 'test',
			Api::CALLABLE => function(Request $request) use ($response) {
				return $response;
			},
			Api::REQUEST => $this->request
		]);

		$this->request->expects()->getMethod()->twice()->andReturn('GET');
		$this->request->expects()->get('key')->twice()->andReturn(null);

		$this->collection->addAction($api);

		$this->assertSame($api, $this->collection->getById($api->id()));

		$response->expects()->send();

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($api) {
				call_user_func([$this->collection, (string) $api->id()]);
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
	public function testApiReturnsJsonResponseOnInvalidApiKey()
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

		$this->collection->addAction($api);

		$this->assertSame($api, $this->collection->getById($api->id()));

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($api) {
				$response = call_user_func([$this->collection, (string) $api->id()]);

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
		$api = Api::create([
			Api::HOOK => 'test',
			Api::CALLABLE => function(Request $request) {

			},
			Api::REQUEST => $this->request
		]);

		$this->request->expects()->getMethod()->twice()->andReturn('POST');
		$this->request->expects()->get('key')->twice()->andReturn(null);

		$this->collection->addAction($api);

		$this->assertSame($api, $this->collection->getById($api->id()));

		WP_Mock::onAction('test')
			->with(null)
			->perform(function() use ($api) {
				$response = call_user_func([$this->collection, (string) $api->id()]);

				$this->assertInstanceOf(JsonResponse::class, $response);
				$this->assertEquals($response->getContent(), json_encode(Api::INVALID_METHOD));
				$this->assertEquals($response->getStatusCode(), Response::HTTP_NOT_FOUND);
			});

		do_action('test');
	}
}