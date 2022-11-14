<?php

namespace JayWolfeLib\Controllers;

use JayWolfeLib\Container;
use JayWolfeLib\Views\ViewInterface;
use JayWolfeLib\Input;
use JayWolfeLib\Factory\ModelFactoryInterface;
use JayWolfeLib\Hooks\Hooks;
use JayWolfeLib\Exception\InvalidView;

use function JayWolfeLib\container;

/**
 * The controller base class.
 */
abstract class Controller implements ControllerInterface
{
	/** @var Input */
	protected $input;

	/** @var ModelFactoryInterface */
	protected $models;

	/** @var ViewInterface */
	protected $view;

	/**
	 * Constructor.
	 *
	 * @param Input $input
	 * @param ModelFactoryInterface $models
	 * @param ConfigInterface $config
	 */
	public function __construct(Input $input, ModelFactoryInterface $models, ViewInterface $view)
	{
		$this->input = $input;
		$this->models = $models;
		$this->view = $view;
	}
}