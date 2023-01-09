<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Component\WordPress\PostType;

use JayWolfeLib\Component\WordPress\PostType\PostTypeCollection;
use JayWolfeLib\Component\WordPress\PostType\PostTypeInterface;
use JayWolfeLib\Component\WordPress\PostType\PostType;
use JayWolfeLib\Component\WordPress\PostType\PostTypeId;
use WP_Mock;
use Mockery;

class PostTypeCollectionTest extends \WP_Mock\Tools\TestCase
{
	private PostTypeCollection $collection;
	private \WP_Error $wp_error;

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
	 */
	public function testCanRegisterPostType()
	{
		$post_type = $this->createPostType();

		WP_Mock::userFunction('register_post_type', [
			'args' => ['test', []],
			'return' => $post_type,
			'times' => 1
		]);

		$this->collection->register_post_type($post_type);
		$this->assertContains($post_type, $this->collection->all());
		$this->assertSame($post_type, $this->collection->get_by_id($post_type->id()));
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @group collection
	 */
	public function testShouldThrowExceptionOnWpError()
	{
		$post_type = $this->createPostType();

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

	/**
	 * @group post_type
	 * @group wordpress
	 * @group collection
	 * @depends testCanGetPostType
	 */
	public function testCanUnregisterPostType()
	{
		$post_type = $this->createPostType();

		WP_Mock::userFunction('register_post_type', [
			'args' => ['test', []],
			'return' => $post_type,
			'times' => 1
		]);

		$this->collection->register_post_type($post_type);
		$this->assertContains($post_type, $this->collection->all());

		WP_Mock::userFunction('unregister_post_type', [
			'args' => 'test',
			'return' => $post_type,
			'times' => 1
		]);

		$bool = $this->collection->unregister_post_type('test');
		$this->assertTrue($bool);
		$this->assertNotContains($post_type, $this->collection->all());
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @group collection
	 * @depends testCanGetPostType
	 */
	public function testUnregisterPostTypeShouldThrowExceptionOnWpError()
	{
		$post_type = $this->createPostType();

		WP_Mock::userFunction('register_post_type', [
			'args' => ['test', []],
			'return' => $post_type,
			'times' => 1
		]);

		$this->collection->register_post_type($post_type);
		$this->assertContains($post_type, $this->collection->all());

		WP_Mock::userFunction('unregister_post_type', [
			'args' => 'test',
			'return' => $this->wp_error,
			'times' => 1
		]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Error removing post type test');

		$this->collection->unregister_post_type('test');
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @group collection
	 */
	public function testUnregisterPostTypeReturnsFalseOnInvalidKey()
	{
		$bool = $this->collection->unregister_post_type('test');
		$this->assertFalse($bool);
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @group collection
	 */
	public function testCanGetPostType()
	{
		$post_type = $this->createPostType();

		WP_Mock::userFunction('register_post_type', [
			'args' => ['test', []],
			'return' => $post_type,
			'times' => 1
		]);

		$this->collection->register_post_type($post_type);
		$this->assertSame($post_type, $this->collection->get('test'));
	}

	public function testGetPostTypeReturnsNullIfNotFound()
	{
		$this->assertArrayNotHasKey('test', $this->collection->all());
		$obj = $this->collection->get('test');
		$this->assertNull($obj);
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @group collection
	 * @depends testCanGetPostType
	 */
	public function testCanRegisterTaxonomy()
	{
		$post_type = $this->createPostType();

		WP_Mock::userFunction('register_post_type', [
			'args' => [$post_type->post_type(), $post_type->args()],
			'return' => $post_type,
			'times' => 1
		]);

		$this->collection->register_post_type($post_type);

		$this->assertSame($post_type, $this->collection->get_by_id($post_type->id()));

		WP_Mock::userFunction('register_taxonomy', ['times' => 1]);
		WP_Mock::userFunction('did_action', [
			'args' => sprintf('registered_post_type_%s', $post_type->post_type()),
			'return' => true,
			'times' => 1
		]);

		$this->collection->register_taxonomy('test', $post_type->post_type());
	}

	private function createPostType(): PostTypeInterface
	{
		return new PostType('test', []);
	}
}