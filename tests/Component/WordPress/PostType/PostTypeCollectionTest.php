<?php

namespace JayWolfeLib\Tests\Component\WordPress\PostType;

use JayWolfeLib\Component\WordPress\PostType\PostTypeCollection;
use JayWolfeLib\Component\WordPress\PostType\PostTypeInterface;
use JayWolfeLib\Component\WordPress\PostType\PostType;
use WP_Mock;
use Mockery;

class PostTypeCollectionTest extends \WP_Mock\Tools\TestCase
{
	private $collection;
	private $wp_error;

	public function setUp(): void
	{
		$this->collection = new PostTypeCollection();
		$this->wp_error = Mockery::mock(\WP_Error::class);
		WP_Mock::setUp();
		WP_Mock::alias('is_wp_error', function($thing) {
			return $thing instanceof \WP_Error;
		});
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @group collection
	 * @doesNotPerformAssertions
	 */
	public function testCanRegisterPostType()
	{
		$post_type = Mockery::mock(PostTypeInterface::class);
		$post_type->expects()->id()->andReturn(spl_object_hash($post_type));
		$post_type->expects()->post_type()->andReturn('test');
		$post_type->expects()->args()->andReturn([]);

		WP_Mock::userFunction('register_post_type', [
			'args' => ['test', []],
			'return' => $post_type,
			'times' => 1
		]);

		$this->collection->register_post_type($post_type);
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @group collection
	 */
	public function testShouldThrowExceptionOnWpError()
	{
		$post_type = Mockery::mock(PostTypeInterface::class);
		$post_type->expects()->id()->andReturn(spl_object_hash($post_type));
		$post_type->expects()->post_type()->twice()->andReturn('test');
		$post_type->expects()->args()->andReturn([]);

		WP_Mock::userFunction('register_post_type', [
			'args' => ['test', []],
			'return' => $this->wp_error,
			'times' => 1
		]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage(
			sprintf('Error registering post type test.')
		);

		$this->collection->register_post_type($post_type);
	}
}