<?php

namespace JayWolfeLib\Tests\Component\WordPress\MetaBox;

use JayWolfeLib\Component\WordPress\MetaBox\MetaBoxCollection;
use JayWolfeLib\Component\WordPress\MetaBox\MetaBoxInterface;
use JayWolfeLib\Component\WordPress\MetaBox\MetaBox;
use JayWolfeLib\Tests\Component\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Symfony\Component\HttpFoundation\Response;
use WP_Mock;
use Mockery;

class MetaBoxCollectionTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private $collection;
	private $post;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
		$this->collection = new MetaBoxCollection($this->container);
		$this->post = Mockery::mock(\WP_Post::class);
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
		$mb = Mockery::mock(MetaBoxInterface::class);
		$mb->expects()->id()->twice()->andReturn(spl_object_hash($mb));
		$mb->expects()->meta_id()->andReturn('test');
		$mb->expects()->title()->andReturn('test');
		$mb->expects()->get('screen')->andReturn(null);
		$mb->expects()->get('context')->andReturn('advanced');
		$mb->expects()->get('priority')->andReturn('default');
		$mb->expects()->get('callback_args')->andReturn(null);

		$this->collection->add_meta_box($mb);
		$this->assertContains($mb, $this->collection->all());
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testCanRemoveMetaBox()
	{
		$mb = Mockery::mock(MetaBoxInterface::class);
		$mb->expects()->id()->times(3)->andReturn(spl_object_hash($mb));
		$mb->expects()->meta_id()->times(3)->andReturn('test');
		$mb->expects()->title()->andReturn('test');
		$mb->expects()->get('screen')->times(3)->andReturn(null);
		$mb->expects()->get('context')->times(3)->andReturn('advanced');
		$mb->expects()->get('priority')->andReturn('default');
		$mb->expects()->get('callback_args')->andReturn(null);

		$this->collection->add_meta_box($mb);
		$this->assertContains($mb, $this->collection->all());

		$bool = $this->collection->remove_meta_box('test', null, 'advanced');
		$this->assertTrue($bool);

		$this->assertNotContains($mb, $this->collection->all());
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testRemoveMetaBoxReturnsFalseOnInvalidKey()
	{
		$this->assertArrayNotHasKey('test', $this->collection->all());
		$bool = $this->collection->remove_meta_box('test', null, 'advanced');
		$this->assertFalse($bool);
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testCanGetMetaBox()
	{
		$mb = Mockery::mock(MetaBoxInterface::class);
		$mb->expects()->id()->twice()->andReturn(spl_object_hash($mb));
		$mb->expects()->meta_id()->andReturn('test');
		$mb->expects()->title()->andReturn('test');
		$mb->expects()->get('screen')->andReturn(null);
		$mb->expects()->get('context')->andReturn('advanced');
		$mb->expects()->get('priority')->andReturn('default');
		$mb->expects()->get('callback_args')->andReturn(null);

		$this->collection->add_meta_box($mb);
		$this->assertSame($mb, $this->collection->get(spl_object_hash($mb)));
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeMetaBox()
	{
		$mb = new MetaBox('test', 'test', function(\WP_Post $post) {
			$this->assertTrue(true);
		});

		$this->collection->add_meta_box($mb);

		$this->assertSame($mb, $this->collection->get($mb->id()));
		call_user_func([$this->collection, $mb->id()], $this->post);
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeMetaBoxWithTypeHint()
	{
		$mb = new MetaBox('test', 'test', function(MockTypeHint $th, \WP_Post $post) {
			$this->assertInstanceOf(MockTypeHint::class, $th);
		}, ['map' => [\DI\get(MockTypeHint::class)]]);

		$this->collection->add_meta_box($mb);

		$this->assertSame($mb, $this->collection->get($mb->id()));
		call_user_func([$this->collection, $mb->id()], $this->post);
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeMetaBoxWithTypeHintAndArguments()
	{
		$mb = new MetaBox(
			'test',
			'test',
			function(MockTypeHint $th, \WP_Post $post, array $args) {
				$this->assertInstanceOf(MockTypeHint::class, $th);
				$this->assertEquals(123, $args['test']);
			},
			[
				'map' => [\DI\get(MockTypeHint::class)],
				'callback_args' => ['test' => 123]
			]
		);

		$this->collection->add_meta_box($mb);

		$this->assertSame($mb, $this->collection->get($mb->id()));
		call_user_func([$this->collection, $mb->id()], $this->post, $mb->get('callback_args'));
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 * @group collection
	 */
	public function testMetaBoxCanHandleResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$mb = new MetaBox('test', 'test', function(\WP_Post $post) use ($response){
			$this->assertTrue(true);
			return $response;
		});

		$this->collection->add_meta_box($mb);

		$this->assertSame($mb, $this->collection->get($mb->id()));

		$response->expects()->send();

		call_user_func([$this->collection, $mb->id()], $this->post);
	}
}