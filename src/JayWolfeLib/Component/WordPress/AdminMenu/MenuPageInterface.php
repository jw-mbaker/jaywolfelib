<?php

namespace JayWolfeLib\Component\WordPress\AdminMenu;

use JayWolfeLib\Component\HandlerInterface;
use JayWolfeLib\Component\ParameterInterface;
use JayWolfeLib\Component\ObjectHash\ObjectHashInterface;

interface MenuPageInterface extends HandlerInterface, ParameterInterface, ObjectHashInterface
{
	public function slug(): string;
}