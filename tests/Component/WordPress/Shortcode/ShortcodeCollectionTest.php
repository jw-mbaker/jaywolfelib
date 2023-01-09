<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Component\WordPress\Shortcode;

use JayWolfeLib\Component\WordPress\Shortcode\ShortcodeCollection;
use JayWolfeLib\Component\WordPress\Shortcode\ShortcodeInterface;
use JayWolfeLib\Component\WordPress\Shortcode\Shortcode;
use JayWolfeLib\Component\WordPress\Shortcode\ShortcodeId;
use JayWolfeLib\Tests\Invoker\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use WP_Mock;
use Mockery;

class ShortcodeCollectionTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private ShortcodeCollection $collection;

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
		$shortcode = $this->createShortcode();

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
		$shortcode = $this->createShortcode();

		$this->collection->add_shortcode($shortcode);
		$this->assertContains($shortcode, $this->collection->all());

		$bool = $this->collection->remove_shortcode('test');
		$this->assertTrue($bool);

		$this->assertNotContains($shortcode, $this->collection->all());
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 * @group collection
	 */
	public function testRemoveShortCodeReturnsFalseOnInvalidKey()
	{
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
		$shortcode = $this->createShortcode();

		$this->collection->add_shortcode($shortcode);
		$this->assertSame($shortcode, $this->collection->get_by_id($shortcode->id()));
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeShortcode()
	{
		$shortcode = Shortcode::create([
			Shortcode::TAG => 'test',
			Shortcode::CALLABLE => function(array $atts, string $content = '') {
				$this->assertEquals(123, $atts['test']);
				return $content;
			}
		]);

		$this->collection->add_shortcode($shortcode);

		$this->assertSame($shortcode, $this->collection->get_by_id($shortcode->id()));

		$content = call_user_func([$this->collection, (string) $shortcode->id()], ['test' => 123], 'xyz');
		$this->assertEquals('xyz', $content);
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeShortcodeWithTypeHint()
	{
		$shortcode = Shortcode::create([
			Shortcode::TAG => 'test',
			Shortcode::CALLABLE => function(MockTypeHint $th, array $atts, string $content = '') {
				$this->assertInstanceOf(MockTypeHint::class, $th);
				$this->assertEquals(123, $atts['test']);
				return $content;
			},
			Shortcode::MAP => [\DI\get(MockTypeHint::class)]
		]);

		$this->collection->add_shortcode($shortcode);

		$this->assertSame($shortcode, $this->collection->get_by_id($shortcode->id()));

		$content = call_user_func([$this->collection, (string) $shortcode->id()], ['test' => 123], 'xyz');
		$this->assertEquals('xyz', $content);
	}

	private function createShortcode(): ShortcodeInterface
	{
		return Shortcode::create([
			Shortcode::TAG => 'test',
			Shortcode::CALLABLE => function() {}
		]);
	}
}