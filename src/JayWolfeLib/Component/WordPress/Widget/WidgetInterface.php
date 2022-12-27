<?php

namespace JayWolfeLib\Component\WordPress\Widget;

use JayWolfeLib\Component\ObjectHash\ObjectHashInterface;

interface WidgetInterface extends ObjectHashInterface
{
	/** @return string|\WP_Widget */
	public function widget();
}