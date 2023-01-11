<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\WordPress\PostType;

use JayWolfeLib\WordPress\PostType\PostType;
use JayWolfeLib\WordPress\PostType\PostTypeId;
use WP_Mock;

class PostTypeTest extends \WP_Mock\Tools\TestCase
{
	public function setUp(): void
	{
		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
	}

	/**
	 * @group post_type
	 * @group wordpress
	 */
	public function testCanGetPostTypeAndArgs()
	{
		$postType = new PostType('test', ['label' => 'test123']);
		$this->assertEquals('test', $postType->postType());
		$this->assertEquals('test123', $postType->args()['label']);
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @doesNotPerformAssertions
	 */
	public function testShouldAddActionToRegisterTaxonomy()
	{
		$postType = new PostType('test', []);

		WP_Mock::userFunction('did_action', ['return' => false, 'times' => 1]);
		WP_Mock::expectActionAdded(sprintf('registered_post_type_%s', $postType->postType()), function() {});

		$postType->registerTaxonomy('test');
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @doesNotPerformAssertions
	 */
	public function testShouldCallCallbackOnRegisterTaxonomy()
	{
		$postType = new PostType('test', []);

		WP_Mock::userFunction('did_action', ['return' => true, 'times' => 1]);
		WP_Mock::userFunction('register_taxonomy', [
			'args' => ['test', $postType->postType(), []],
			'times' => 1
		]);

		$postType->registerTaxonomy('test');
	}

	/**
	 * @group post_type
	 * @group wordpress
	 */
	public function testPostTypeIdMethodShouldReturnPostTypeId()
	{
		$postType = new PostType('test', []);

		$id = $postType->id();
		$this->assertInstanceOf(PostTypeId::class, $id);
		$this->assertSame((string) $id, spl_object_hash($postType));
	}
}