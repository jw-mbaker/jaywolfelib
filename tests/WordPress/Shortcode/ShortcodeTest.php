<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\WordPress\Shortcode;

use JayWolfeLib\WordPress\Shortcode\ShortcodeInterface;
use JayWolfeLib\WordPress\Shortcode\Shortcode;
use JayWolfeLib\WordPress\Shortcode\ShortcodeId;
use JayWolfeLib\Tests\Invoker\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use WP_Mock;

class ShortcodeTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 */
	public function testCanInvokeShortcode()
	{
		$shortcode = Shortcode::create([
			Shortcode::TAG => 'test',
			Shortcode::CALLABLE => function(array $atts) {
				$this->assertEquals(123, $atts['test']);
			}
		]);

		$this->container->call($shortcode, [$this->container, ['test' => 123]]);
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 */
	public function testCanInvokeShortcodeWithTypeHint()
	{
		$shortcode = Shortcode::create([
			Shortcode::TAG => 'test',
			Shortcode::CALLABLE => function(MockTypeHint $th, array $atts) {
				$this->assertEquals(123, $atts['test']);
			},
			Shortcode::MAP => [\DI\get(MockTypeHint::class)]
		]);

		$this->container->call($shortcode, [$this->container, ...$shortcode->map(), ['test' => 123]]);
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 */
	public function testCanGetId()
	{
		$shortcode = Shortcode::create([
			Shortcode::TAG => 'test',
			Shortcode::CALLABLE => function() {}
		]);

		$this->assertInstanceOf(ShortcodeId::class, $shortcode->id());
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 */
	public function testCanGetTag()
	{
		$shortcode = Shortcode::create([
			Shortcode::TAG => 'test',
			Shortcode::CALLABLE => function() {}
		]);

		$this->assertEquals($shortcode->tag(), 'test');
	}
}