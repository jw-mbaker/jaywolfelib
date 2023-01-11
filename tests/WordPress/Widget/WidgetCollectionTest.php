<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\WordPress\Widget;

use JayWolfeLib\WordPress\Widget\WidgetCollection;
use JayWolfeLib\WordPress\Widget\WidgetInterface;
use JayWolfeLib\WordPress\Widget\Widget;
use JayWolfeLib\WordPress\Widget\WidgetId;
use WP_Widget;
use WP_Mock;
use Mockery;

class WidgetCollectionTest extends \WP_Mock\Tools\TestCase
{
	private WidgetCollection $collection;
	private WP_Widget $wp_widget;

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
		$widget = $this->createWidget();

		$this->mockRegisterWidget();

		$this->collection->registerWidget($widget);
		$this->assertContains($widget, $this->collection->all());
		$this->assertSame($widget, $this->collection->getById($widget->id()));
	}

	/**
	 * @group widget
	 * @group wordpress
	 * @group collection
	 * @depends testCanGetWidget
	 */
	public function testCanUnregisterWidget()
	{
		$widget = $this->createWidget();

		$this->mockRegisterWidget();

		$this->collection->registerWidget($widget);
		$this->assertContains($widget, $this->collection->all());

		$this->mockUnregisterWidget();

		$bool = $this->collection->unregisterWidget($this->wp_widget);
		$this->assertTrue($bool);
		$this->assertNotContains($widget, $this->collection->all());
	}

	/**
	 * @group widget
	 * @group wordpress
	 * @group collection
	 */
	public function testCanGetWidget()
	{
		$widget = $this->createWidget();

		$this->mockRegisterWidget();

		$this->collection->registerWidget($widget);
		$this->assertContains($widget, $this->collection->all());

		$obj = $this->collection->get($this->wp_widget);
		$this->assertInstanceOf(WidgetInterface::class, $obj);
		$this->assertSame($obj, $widget);
	}

	/**
	 * @group widget
	 * @group wordpress
	 * @group collection
	 */
	public function testGetWidgetReturnsNullIfNotFound()
	{
		$widget = $this->collection->get($this->wp_widget);
		$this->assertNull($widget);
	}

	private function createWidget(): WidgetInterface
	{
		return new Widget($this->wp_widget);
	}

	private function mockRegisterWidget(int $times = 1): void
	{
		WP_Mock::userFunction('register_widget', [
			'args' => [$this->wp_widget],
			'times' => $times
		]);
	}

	private function mockUnregisterWidget(int $times = 1): void
	{
		WP_Mock::userFunction('unregister_widget', [
			'args' => [$this->wp_widget],
			'times' => $times
		]);
	}
}