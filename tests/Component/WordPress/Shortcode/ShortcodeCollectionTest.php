<?php

namespace JayWolfeLib\Tests\Component\WordPress\Shortcode;

use JayWolfeLib\Component\WordPress\Shortcode\ShortcodeCollection;
use JayWolfeLib\Component\WordPress\Shortcode\ShortcodeInterface;
use JayWolfeLib\Component\WordPress\Shortcode\Shortcode;
use JayWolfeLib\Tests\Component\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use WP_Mock;
use Mockery;

class ShortcodeCollectionTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private $collection;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
		$this->collection = new ShortcodeCollection($this->container);
		WP_Mock::setUp();
		WP_Mock::userFunction('add_shortcode');
		WP_Mock::userFunction('remove_shortcode');
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 * @group collection
	 */
	public function testCanAddShortcode()
	{
		$shortcode = Mockery::mock(ShortcodeInterface::class);
		$shortcode->expects()->id()->twice()->andReturn(spl_object_hash($shortcode));
		$shortcode->expects()->tag()->andReturn('test');

		$this->collection->add_shortcode($shortcode);
		$this->assertContains($shortcode, $this->collection->all());
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 * @group collection
	 */
	public function testCanRemoveShortcode()
	{
		$shortcode = Mockery::mock(ShortcodeInterface::class);
		$shortcode->expects()->id()->times(3)->andReturn(spl_object_hash($shortcode));
		$shortcode->expects()->tag()->times(3)->andReturn('test');

		$this->collection->add_shortcode($shortcode);
		$this->assertContains($shortcode, $this->collection->all());

		$bool = $this->collection->remove_shortcode('test');
		$this->assertTrue($bool);

		$this->assertNotContains($bool, $this->collection->all());
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 * @group collection
	 */
	public function testRemoveShortCodeReturnsFalseOnInvalidKey()
	{
		$this->assertArrayNotHasKey('test', $this->collection->all());
		$bool = $this->collection->remove_shortcode('test');
		$this->assertFalse($bool);
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 * @group collection
	 */
	public function testCanGetShortcode()
	{
		$shortcode = Mockery::mock(ShortcodeInterface::class);
		$shortcode->expects()->id()->twice()->andReturn(spl_object_hash($shortcode));
		$shortcode->expects()->tag()->andReturn('test');

		$this->collection->add_shortcode($shortcode);
		$this->assertSame($shortcode, $this->collection->get(spl_object_hash($shortcode)));
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeShortcode()
	{
		$shortcode = new Shortcode('test', function(array $atts, string $content = '') {
			$this->assertEquals(123, $atts['test']);
			return $content;
		});

		$this->collection->add_shortcode($shortcode);

		$this->assertSame($shortcode, $this->collection->get($shortcode->id()));

		$content = call_user_func([$this->collection, $shortcode->id()], ['test' => 123], 'xyz');
		$this->assertEquals('xyz', $content);
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeShortcodeWithTypeHint()
	{
		$shortcode = new Shortcode('test', function(MockTypeHint $th, array $atts, string $content = '') {
			$this->assertInstanceOf(MockTypeHint::class, $th);
			$this->assertEquals(123, $atts['test']);
			return $content;
		}, ['map' => [\DI\get(MockTypeHint::class)]]);

		$this->collection->add_shortcode($shortcode);

		$this->assertSame($shortcode, $this->collection->get($shortcode->id()));

		$content = call_user_func([$this->collection, $shortcode->id()], ['test' => 123], 'xyz');
		$this->assertEquals('xyz', $content);
	}
}