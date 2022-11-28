<?php

namespace JayWolfeLib\Controllers;

use JayWolfeLib\Container;
use JayWolfeLib\Views\ViewInterface;
use JayWolfeLib\Config\ConfigTrait;

/**
 * The controller base class.
 */
abstract class Controller implements ControllerInterface
{
	use ConfigTrait;

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