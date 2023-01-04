<?php

namespace JayWolfeLib\Tests\Component\WordPress\MetaBox;

use JayWolfeLib\Component\WordPress\MetaBox\MetaBox;
use JayWolfeLib\Tests\Component\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Symfony\Component\HttpFoundation\Response;
use Mockery;

class MetaBoxTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private $post;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
		$this->post = Mockery::mock(\WP_Post::class);
	}

	public function tearDown(): void
	{
		Mockery::close();
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 */
	public function testCanInvokeMetaBox()
	{
		$mb = new MetaBox('test', 'test', function(\WP_Post $post) {
			$this->assertInstanceOf(\WP_Post::class, $post);
		});

		$this->container->call($mb, [$this->container, $this->post]);
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 */
	public function testCanInvokeMetaBoxWithTypeHint()
	{
		$mb = new MetaBox('test', 'test', function(MockTypeHint $th, \WP_Post $post) {
			$this->assertInstanceOf(MockTypeHint::class, $th);
			$this->assertInstanceOf(\WP_Post::class, $post);
		}, ['map' => [\DI\get(MockTypeHint::class)]]);

		$this->container->call($mb, [$this->container, ...$mb->get('map'), $this->post]);
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 */
	public function testCanGetId()
	{
		$mb = new MetaBox('test', 'test', function() {});
		$this->assertNotNull($mb->id());
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 */
	public function testCanGetMetaId()
	{
		$mb = new MetaBox('test', 'test', function() {});
		$this->assertEquals('test', $mb->meta_id());
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 */
	public function testMetaBoxCanHandleResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$mb = new MetaBox('test', 'test', function() use ($response) {
			$this->assertTrue(true);
			return $response;
		});

		$response = $this->container->call($mb);
		$this->assertInstanceOf(Response::class, $response);
	}
}