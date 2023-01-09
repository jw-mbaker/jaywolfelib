<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Component\WordPress\MetaBox;

use JayWolfeLib\Component\WordPress\MetaBox\MetaBox;
use JayWolfeLib\Component\WordPress\MetaBox\MetaBoxId;
use JayWolfeLib\Tests\Invoker\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Symfony\Component\HttpFoundation\Response;
use InvalidArgumentException;
use WP_Post;
use Mockery;

class MetaBoxTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private WP_Post $post;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
		$this->post = Mockery::mock(WP_Post::class);
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
		$mb = MetaBox::create([
			MetaBox::META_ID => 'test',
			MetaBox::TITLE => 'test',
			MetaBox::CALLABLE => function(WP_Post $post) {
				$this->assertInstanceOf(WP_Post::class, $post);
			}
		]);

		$this->container->call($mb, [$this->container, $this->post]);
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 */
	public function testCanInvokeMetaBoxWithTypeHint()
	{
		$mb = MetaBox::create([
			MetaBox::META_ID => 'test',
			MetaBox::TITLE => 'test',
			MetaBox::CALLABLE => function(MockTypeHint $th, WP_Post $post) {
				$this->assertInstanceOf(MockTypeHint::class, $th);
				$this->assertInstanceOf(WP_Post::class, $post);
			},
			MetaBox::MAP => [\DI\get(MockTypeHint::class)]
		]);

		$this->container->call($mb, [$this->container, ...$mb->map(), $this->post]);
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 */
	public function testCanGetId()
	{
		$mb = MetaBox::create([
			MetaBox::META_ID => 'test',
			MetaBox::TITLE => 'test',
			MetaBox::CALLABLE => function() {}
		]);

		$id = $mb->id();
		$this->assertInstanceOf(MetaBoxId::class, $id);
		$this->assertSame((string) $id, spl_object_hash($mb));
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 */
	public function testCanGetMetaId()
	{
		$mb = MetaBox::create([
			MetaBox::META_ID => 'test',
			MetaBox::TITLE => 'test',
			MetaBox::CALLABLE => function() {}
		]);

		$this->assertEquals('test', $mb->meta_id());
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 */
	public function testMetaBoxCanHandleResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$mb = MetaBox::create([
			MetaBox::META_ID => 'test',
			MetaBox::TITLE => 'test',
			MetaBox::CALLABLE => function() use ($response) {
				$this->assertTrue(true);
				return $response;
			}
		]);

		$response = $this->container->call($mb);
		$this->assertInstanceOf(Response::class, $response);
	}

	/**
	 * @group meta_box
	 * @group wordpress
	 */
	public function testShouldThrowInvalidArgumentExceptionOnInvalidScreen()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid value passed to $screen.');

		$mb = MetaBox::create([
			MetaBox::META_ID => 'test',
			MetaBox::TITLE => 'test',
			MetaBox::CALLABLE => function() {},
			MetaBox::SCREEN => 123
		]);
	}
}