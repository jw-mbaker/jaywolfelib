<?php

namespace JayWolfeLib\Tests\Component\WordPress\Widget;

use JayWolfeLib\Component\WordPress\Widget\WidgetCollection;
use JayWolfeLib\Component\WordPress\Widget\WidgetInterface;
use JayWolfeLib\Component\WordPress\Widget\Widget;
use WP_Widget;
use WP_Mock;
use Mockery;

class WidgetCollectionTest extends \WP_Mock\Tools\TestCase
{
	private $collection;
	private $wp_widget;

	public function setUp(): void
	{
		$this->collection = new WidgetCollection();
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
	 * @group collection
	 */
	public function testCanRegisterWidget()
	{
		$widget = Mockery::mock(WidgetInterface::class);
		$widget->expects()->id()->andReturn(spl_object_hash($widget));
		$widget->expects()->widget()->andReturn($this->wp_widget);

		WP_Mock::userFunction('register_widget', [
			'args' => [$this->wp_widget],
			'times' => 1
		]);

		$this->collection->register_widget($widget);
		$this->assertContains($widget, $this->collection->all());
	}

	/**
	 * @group widget
	 * @group wordpress
	 * @group collection
	 * @depends testCanGetByWpWidget
	 */
	public function testCanUnregisterWidget()
	{
		$widget = Mockery::mock(WidgetInterface::class);
		$widget->expects()->id()->twice()->andReturn(spl_object_hash($widget));
		$widget->expects()->widget()->times(3)->andReturn($this->wp_widget);

		WP_Mock::userFunction('register_widget', [
			'args' => [$this->wp_widget],
			'times' => 1
		]);

		$this->collection->register_widget($widget);
		$this->assertContains($widget, $this->collection->all());

		WP_Mock::userFunction('unregister_widget', [
			'args' => [$this->wp_widget],
			'times' => 1
		]);

		$bool = $this->collection->unregister_widget($this->wp_widget);
		$this->assertTrue($bool);
		$this->assertNotContains($widget, $this->collection->all());
	}

	/**
	 * @group widget
	 * @group wordpress
	 * @group collection
	 */
	public function testCanGetByWpWidget()
	{
		$widget = Mockery::mock(WidgetInterface::class);
		$widget->expects()->id()->andReturn(spl_object_hash($widget));
		$widget->expects()->widget()->twice()->andReturn($this->wp_widget);

		WP_Mock::userFunction('register_widget', [
			'args' => [$this->wp_widget],
			'times' => 1
		]);

		$this->collection->register_widget($widget);
		$this->assertContains($widget, $this->collection->all());

		$obj = $this->collection->get_by_wp_widget($this->wp_widget);
		$this->assertInstanceOf(WidgetInterface::class, $obj);
		$this->assertSame($obj, $widget);
	}

	/**
	 * @group widget
	 * @group wordpress
	 * @group collection
	 */
	public function testGetByWpWidgetReturnsNullIfNotFound()
	{
		$widget = $this->collection->get_by_wp_widget($this->wp_widget);
		$this->assertNull($widget);
	}
}