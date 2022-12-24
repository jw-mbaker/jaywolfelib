<?php

namespace JayWolfeLib\Component\WordPress\Shortcode;

use JayWolfeLib\Component\HandlerInterface;
use JayWolfeLib\Component\ParameterInterface;
use JayWolfeLib\Component\ObjectHash\ObjectHashInterface;

interface ShortcodeInterface extends
	HandlerInterface,
	ParameterInterface,
	ObjectHashInterface
{
	public function tag(): string;
}