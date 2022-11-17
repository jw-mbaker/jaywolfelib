<?php

namespace JayWolfeLib\Controllers;

use JayWolfeLib\Container;
use JayWolfeLib\Views\ViewInterface;
use JayWolfeLib\Input;
use JayWolfeLib\Factory\ModelFactoryInterface;
use JayWolfeLib\Hooks\Hooks;

use function JayWolfeLib\container;

/**
 * The controller base class.
 */
abstract class Controller implements ControllerInterface
{
	/** @var ViewInterface */
	protected $view;

	/**
	 * Constructor.
	 *
	 * @param ViewInterface $view
	 */
	public function __construct(ViewInterface $view)
	{
		$this->view = $view;
	}
}