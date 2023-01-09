<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Component\WordPress\PostType;

use JayWolfeLib\Component\WordPress\PostType\PostType;
use JayWolfeLib\Component\WordPress\PostType\PostTypeId;
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
		$post_type = new PostType('test', ['label' => 'test123']);
		$this->assertEquals('test', $post_type->post_type());
		$this->assertEquals('test123', $post_type->args()['label']);
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @doesNotPerformAssertions
	 */
	public function testShouldAddActionToRegisterTaxonomy()
	{
		$post_type = new PostType('test', []);

		WP_Mock::userFunction('did_action', ['return' => false, 'times' => 1]);
		WP_Mock::expectActionAdded(sprintf('registered_post_type_%s', $post_type->post_type()), function() {});

		$post_type->register_taxonomy('test');
	}

	/**
	 * @group post_type
	 * @group wordpress
	 * @doesNotPerformAssertions
	 */
	public function testShouldCallCallbackOnRegisterTaxonomy()
	{
		$post_type = new PostType('test', []);

		WP_Mock::userFunction('did_action', ['return' => true, 'times' => 1]);
		WP_Mock::userFunction('register_taxonomy', [
			'args' => ['test', $post_type->post_type(), []],
			'times' => 1
		]);

		$post_type->register_taxonomy('test');
	}

	/**
	 * @group post_type
	 * @group wordpress
	 */
	public function testPostTypeIdMethodShouldReturnPostTypeId()
	{
		$post_type = new PostType('test', []);

		$id = $post_type->id();
		$this->assertInstanceOf(PostTypeId::class, $id);
		$this->assertSame((string) $id, spl_object_hash($post_type));
	}
}