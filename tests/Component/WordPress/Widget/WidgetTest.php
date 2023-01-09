<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Component\WordPress\Widget;

use JayWolfeLib\Component\WordPress\Widget\Widget;
use JayWolfeLib\Component\WordPress\Widget\WidgetId;
use WP_Widget;
use WP_Mock;
use Mockery;
use InvalidArgumentException;

class WidgetTest extends \WP_Mock\Tools\TestCase
{
	private WP_Widget $wp_widget;

	public function setUp(): void
	{
		$this->wp_widget = Mockery::mock(WP_Widget::class);
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
		$this->assertInstanceOf(WP_Widget::class, $widget->wp_widget());
		$this->assertSame($this->wp_widget, $widget->wp_widget());
	}

	/**
	 * @group widget
	 * @group wordpress
	 */
	public function testCanPassWpWidgetAsString()
	{
		$widget = new Widget(WP_Widget::class);
		$this->assertEquals(WP_Widget::class, $widget->wp_widget());
	}

	/**
	 * @group widget
	 * @group wordpress
	 */
	public function testCanGetId()
	{
		$widget = new Widget($this->wp_widget);
		$this->assertInstanceOf(WidgetId::class, $widget->id());
		$this->assertSame((string) $widget->id(), spl_object_hash($widget));
	}

	/**
	 * @group widget
	 * @group wordpress
	 */
	public function testShouldThrowInvalidArgumentExceptionOnInvalidWpWidget()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('$wp_widget must be a string or an instance of WP_Widget.');

		$widget = new Widget(false);
	}
}