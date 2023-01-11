<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\WordPress\PostType;

use JayWolfeLib\WordPress\PostType\PostTypeCollection;
use JayWolfeLib\WordPress\PostType\PostTypeInterface;
use JayWolfeLib\WordPress\PostType\PostType;
use JayWolfeLib\WordPress\PostType\PostTypeId;
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
		$postType = $this->createPostType();

		WP_Mock::userFunction('register_post_type', [
			'args' => ['test', []],
			'return' => $postType,
			'times' => 1
		]);

		$this->collection->registerPostType($postType);
		$this->assertContains($postType, $this->collection->all());
		$this->assertSame($postType, $this->collection->getById($postType->id()));
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @group collection
	 */
	public function testShouldThrowExceptionOnWpError()
	{
		$postType = $this->createPostType();

		WP_Mock::userFunction('register_post_type', [
			'args' => ['test', []],
			'return' => $this->wp_error,
			'times' => 1
		]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage(
			sprintf('Error registering post type test.')
		);

		$this->collection->registerPostType($postType);
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @group collection
	 * @depends testCanGetPostType
	 */
	public function testCanUnregisterPostType()
	{
		$postType = $this->createPostType();

		WP_Mock::userFunction('register_post_type', [
			'args' => ['test', []],
			'return' => $postType,
			'times' => 1
		]);

		$this->collection->registerPostType($postType);
		$this->assertContains($postType, $this->collection->all());

		WP_Mock::userFunction('unregister_post_type', [
			'args' => 'test',
			'return' => $postType,
			'times' => 1
		]);

		$bool = $this->collection->unregisterPostType('test');
		$this->assertTrue($bool);
		$this->assertNotContains($postType, $this->collection->all());
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @group collection
	 * @depends testCanGetPostType
	 */
	public function testUnregisterPostTypeShouldThrowExceptionOnWpError()
	{
		$postType = $this->createPostType();

		WP_Mock::userFunction('register_post_type', [
			'args' => ['test', []],
			'return' => $postType,
			'times' => 1
		]);

		$this->collection->registerPostType($postType);
		$this->assertContains($postType, $this->collection->all());

		WP_Mock::userFunction('unregister_post_type', [
			'args' => 'test',
			'return' => $this->wp_error,
			'times' => 1
		]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Error removing post type test');

		$this->collection->unregisterPostType('test');
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @group collection
	 */
	public function testUnregisterPostTypeReturnsFalseOnInvalidKey()
	{
		$bool = $this->collection->unregisterPostType('test');
		$this->assertFalse($bool);
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @group collection
	 */
	public function testCanGetPostType()
	{
		$postType = $this->createPostType();

		WP_Mock::userFunction('register_post_type', [
			'args' => ['test', []],
			'return' => $postType,
			'times' => 1
		]);

		$this->collection->registerPostType($postType);
		$this->assertSame($postType, $this->collection->get('test'));
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
		$postType = $this->createPostType();

		WP_Mock::userFunction('register_post_type', [
			'args' => [$postType->postType(), $postType->args()],
			'return' => $postType,
			'times' => 1
		]);

		$this->collection->registerPostType($postType);

		$this->assertSame($postType, $this->collection->getById($postType->id()));

		WP_Mock::userFunction('register_taxonomy', ['times' => 1]);
		WP_Mock::userFunction('did_action', [
			'args' => sprintf('registered_post_type_%s', $postType->postType()),
			'return' => true,
			'times' => 1
		]);

		$this->collection->registerTaxonomy('test', $postType->postType());
	}

	private function createPostType(): PostTypeInterface
	{
		return new PostType('test', []);
	}
}