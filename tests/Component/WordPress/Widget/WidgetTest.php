<?php

namespace JayWolfeLib\Tests\Component\WordPress\Widget;

use JayWolfeLib\Component\WordPress\Widget\Widget;
use WP_Mock;
use Mockery;

class WidgetTest extends \WP_Mock\Tools\TestCase
{
	private $wp_widget;

	public function setUp(): void
	{
		$this->wp_widget = Mockery::mock(\WP_Widget::class);
		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group widget
	 * @group wordpress
	 */
	public function testCanGetWpWidget()
	{
		$widget = new Widget($this->wp_widget);
		$this->assertInstanceOf(\WP_Widget::class, $widget->widget());
		$this->assertSame($this->wp_widget, $widget->widget());
	}

	/**
	 * @group widget
	 * @group wordpress
	 */
	public function testCanPassWpWidgetAsString()
	{
		$widget = new Widget(\WP_Widget::class);
		$this->assertEquals(\WP_Widget::class, $widget->widget());
	}

	/**
	 * @group widget
	 * @group wordpress
	 */
	public function testCanGetId()
	{
		$widget = new Widget($this->wp_widget);
		$this->assertNotNull($widget->id());
	}
}