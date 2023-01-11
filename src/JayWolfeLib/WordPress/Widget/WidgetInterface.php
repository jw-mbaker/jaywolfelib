<?php

namespace JayWolfeLib\Component\WordPress\Widget;

use WP_Widget;

interface WidgetInterface
{
	/** @return string|WP_Widget */
	public function wp_widget();
}