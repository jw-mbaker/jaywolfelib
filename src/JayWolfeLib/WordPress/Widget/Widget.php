<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\Widget;

use WP_Widget;
use InvalidArgumentException;

class Widget implements WidgetInterface
{
	protected WidgetId $id;

	/**
	 * The widget.
	 * 
	 * @var string|WP_Widget
	 */
	protected $wp_widget;

	/**
	 * Constructor.
	 *
	 * @param string|WP_Widget $widget
	 */
	public function __construct($wp_widget)
	{
		self::validate_wp_widget($wp_widget);

		$this->wp_widget = $wp_widget;
	}

	public function id(): WidgetId
	{
		return $this->id ??= WidgetId::fromWidget($this);
	}
	
	/**
	 * @return string|WP_Widget
	 */
	public function wp_widget()
	{
		return $this->wp_widget;
	}

	/**
	 * Validate the widget.
	 *
	 * @param mixed $wp_widget
	 * @throws InvalidArgumentException
	 */
	private static function validate_wp_widget($wp_widget)
	{
		if (!is_string($wp_widget) && !$wp_widget instanceof WP_Widget) {
			throw new InvalidArgumentException('$wp_widget must be a string or an instance of WP_Widget.');
		}
	}
}