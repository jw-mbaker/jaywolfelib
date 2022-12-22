<?php

namespace JayWolfeLib\Controllers;

use JayWolfeLib\Views\ViewInterface;
use JayWolfeLib\Traits\JayWolfeTrait;

/**
 * The controller base class.
 */
abstract class Controller implements ControllerInterface
{
	use JayWolfeTrait;

	public function __construct()
	{
		
	}
}