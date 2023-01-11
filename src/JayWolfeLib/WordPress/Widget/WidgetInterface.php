<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\Widget;

use WP_Widget;

interface WidgetInterface
{
	/** @return string|WP_Widget */
	public function wpWidget();
}