<?php

namespace JayWolfeLib\Tests\Component\WordPress\Shortcode;

use JayWolfeLib\Component\WordPress\Shortcode\ShortcodeInterface;
use JayWolfeLib\Component\WordPress\Shortcode\Shortcode;
use JayWolfeLib\Tests\Component\MockTypeHint;
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
		$shortcode = new Shortcode('test', function(array $atts) {
			$this->assertEquals(123, $atts['test']);
		});

		$this->container->call($shortcode, [$this->container, ['test' => 123]]);
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 */
	public function testCanInvokeShortcodeWithTypeHint()
	{
		$shortcode = new Shortcode('test', function(MockTypeHint $th, array $atts) {
			$this->assertEquals(123, $atts['test']);
		}, ['map' => [\DI\get(MockTypeHint::class)]]);

		$this->container->call($shortcode, [$this->container, ...$shortcode->get('map'), ['test' => 123]]);
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 */
	public function testCanGetId()
	{
		$shortcode = new Shortcode('test', function() {});
		$this->assertNotNull($shortcode->id());
	}

	/**
	 * @group shortcode
	 * @group wordpress
	 */
	public function testCanGetTag()
	{
		$shortcode = new Shortcode('test', function() {});
		$this->assertEquals($shortcode->tag(), 'test');
	}
}