<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\Widget;

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
	protected $wpWidget;

	/**
	 * Constructor.
	 *
	 * @param string|WP_Widget $wpWidget
	 */
	public function __construct($wpWidget)
	{
		self::validateWpWidget($wpWidget);

		$this->wpWidget = $wpWidget;
	}

	public function id(): WidgetId
	{
		return $this->id ??= WidgetId::fromWidget($this);
	}
	
	/**
	 * @return string|WP_Widget
	 */
	public function wpWidget()
	{
		return $this->wpWidget;
	}

	/**
	 * Validate the widget.
	 *
	 * @param mixed $wpWidget
	 * @throws InvalidArgumentException
	 */
	private static function validateWpWidget($wpWidget)
	{
		if (!is_string($wpWidget) && !$wpWidget instanceof WP_Widget) {
			throw new InvalidArgumentException('$wpWidget must be a string or an instance of WP_Widget.');
		}
	}
}