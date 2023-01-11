<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\WordPress\MetaBox;

use JayWolfeLib\WordPress\MetaBox\MetaBoxCollection;
use JayWolfeLib\WordPress\MetaBox\MetaBoxInterface;
use JayWolfeLib\WordPress\MetaBox\MetaBox;
use JayWolfeLib\Tests\Invoker\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Symfony\Component\HttpFoundation\Response;
use WP_Post;
use WP_Mock;
use Mockery;

class MetaBoxCollectionTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private MetaBoxCollection $collection;
	private WP_Post $post;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
		$this->collection = new MetaBoxCollection($this->container);
		$this->post = Mockery::mock(WP_Post::class);
		WP_Mock::setUp();
		WP_Mock::userFunction('add_meta_box');
		WP_Mock::userFunction('remove_meta_box');
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testCanAddMetaBox()
	{
		$mb = $this->createMetaBox();

		$this->collection->addMetaBox($mb);
		$this->assertContains($mb, $this->collection->all());
		$this->assertSame($mb, $this->collection->getById($mb->id()));
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 * @depends testCanGetMetaBox
	 */
	public function testCanRemoveMetaBox()
	{
		$mb = $this->createMetaBox();

		$this->collection->addMetaBox($mb);
		$this->assertContains($mb, $this->collection->all());

		$bool = $this->collection->removeMetaBox('test', null, 'advanced');
		$this->assertTrue($bool);

		$this->assertNotContains($mb, $this->collection->all());
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 * @depends testCanGetMetaBox
	 */
	public function testRemoveMetaBoxReturnsFalseOnInvalidKey()
	{
		$bool = $this->collection->removeMetaBox('test', null, 'advanced');
		$this->assertFalse($bool);
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testCanGetMetaBox()
	{
		$mb = $this->createMetaBox();

		$this->collection->addMetaBox($mb);
		$this->assertContains($mb, $this->collection->all());

		$obj = $this->collection->get('test', null, 'advanced');
		$this->assertInstanceOf(MetaBoxInterface::class, $obj);
		$this->assertSame($obj, $mb);
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testGetMetaBoxReturnsNullIfNotFound()
	{
		$obj = $this->collection->get('test', null, 'advanced');
		$this->assertNull($obj);
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeMetaBox()
	{
		$mb = MetaBox::create([
			MetaBox::META_ID => 'test',
			MetaBox::TITLE => 'test',
			MetaBox::CALLABLE => function(WP_Post $post) {
				$this->assertTrue(true);
			}
		]);

		$this->collection->addMetaBox($mb);

		$this->assertSame($mb, $this->collection->getById($mb->id()));
		call_user_func([$this->collection, (string) $mb->id()], $this->post);
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeMetaBoxWithTypeHint()
	{
		$mb = MetaBox::create([
			MetaBox::META_ID => 'test',
			MetaBox::TITLE => 'test',
			MetaBox::CALLABLE => function(MockTypeHint $th, WP_Post $post) {
				$this->assertInstanceOf(MockTypeHint::class, $th);
			},
			MetaBox::MAP => [\DI\get(MockTypeHint::class)]
		]);

		$this->collection->addMetaBox($mb);

		$this->assertSame($mb, $this->collection->getById($mb->id()));
		call_user_func([$this->collection, (string) $mb->id()], $this->post);
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeMetaBoxWithTypeHintAndArguments()
	{
		$mb = MetaBox::create([
			MetaBox::META_ID => 'test',
			MetaBox::TITLE => 'test',
			MetaBox::CALLABLE => function(MockTypeHint $th, WP_Post $post, array $args) {
				$this->assertInstanceOf(MockTypeHint::class, $th);
				$this->assertSame(123, $args['test']);
			},
			MetaBox::CALLBACK_ARGS => ['test' => 123],
			MetaBox::MAP => [\DI\get(MockTypeHint::class)]
		]);

		$this->collection->addMetaBox($mb);

		$this->assertSame($mb, $this->collection->getById($mb->id()));
		call_user_func([$this->collection, (string) $mb->id()], $this->post, $mb->callbackArgs());
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testMetaBoxCanHandleResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$mb = MetaBox::create([
			MetaBox::META_ID => 'test',
			MetaBox::TITLE => 'test',
			MetaBox::CALLABLE => function(WP_Post $post) use ($response) {
				$this->assertTrue(true);
				return $response;
			}
		]);

		$this->collection->addMetaBox($mb);

		$this->assertSame($mb, $this->collection->getById($mb->id()));

		$response->expects()->send();

		call_user_func([$this->collection, (string) $mb->id()], $this->post);
	}

	private function createMetaBox(): MetaBoxInterface
	{
		return MetaBox::create([
			MetaBox::META_ID => 'test',
			MetaBox::TITLE => 'test',
			MetaBox::CALLABLE => function() {}
		]);
	}
}