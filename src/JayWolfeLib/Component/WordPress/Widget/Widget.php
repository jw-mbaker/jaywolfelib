<?php

namespace JayWolfeLib\Component\WordPress\Widget;

use JayWolfeLib\Component\ObjectHash\ObjectHashTrait;
use WP_Widget;

class Widget implements WidgetInterface
{
	use ObjectHashTrait;

	public const TYPE = 'widget';

	/**
	 * The widget.
	 * 
	 * @var string|WP_Widget
	 */
	protected $widget;

	/**
	 * Constructor.
	 *
	 * @param string|WP_Widget $widget
	 */
	public function __construct($widget)
	{
		$this->widget = $widget;
		$this->set_id_from_type(static::TYPE);
	}
	
	/**
	 * @return string|WP_Widget
	 */
	public function widget()
	{
		return $this->widget;
	}
}