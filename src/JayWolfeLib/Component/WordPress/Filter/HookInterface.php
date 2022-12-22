<?php

namespace JayWolfeLib\Component\WordPress\Filter;

use JayWolfeLib\Component\HandlerInterface;
use JayWolfeLib\Component\ParameterInterface;
use JayWolfeLib\Component\ObjectHashInterface;

interface HookInterface extends HandlerInterface, ParameterInterface, ObjectHashInterface
{
	public function hook(): string;
}