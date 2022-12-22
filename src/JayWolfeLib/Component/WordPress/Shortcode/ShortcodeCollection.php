<?php

namespace JayWolfeLib\Component\WordPress\Shortcode;

use JayWolfeLib\Collection\AbstractInvokerCollection;

class ShortcodeCollection extends AbstractInvokerCollection
{
	/**
	 * @var array<string, Shortcode>
	 */
	private $shortcodes = [];

	public function add(string $name, Shortcode $shortcode)
	{
		$this->shortcodes[$name] = $shortcode;
	}
}