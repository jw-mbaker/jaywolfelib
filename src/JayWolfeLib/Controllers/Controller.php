<?php

namespace JayWolfeLib\Controllers;

use JayWolfeLib\Input;
use JayWolfeLib\Factory\ModelFactoryInterface;
use JayWolfeLib\Hooks\Hooks;
use JayWolfeLib\Exception\InvalidView;

/**
 * The controller base class.
 */
abstract class Controller implements ControllerInterface
{
	/** @var Input */
	protected $input;

	/** @var ModelFactoryInterface */
	protected $models;

	/**
	 * Associative array of data which will be automatically
	 * available as variables when template is rendered.
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Constructor.
	 *
	 * @param Input $input
	 * @param ModelFactoryInterface $models
	 */
	public function __construct(Input $input, ModelFactoryInterface $models)
	{
		$this->input = $input;
		$this->models = $models;
	}

	public function render(string $view)
	{
		$views_path = Hooks::apply_filters('jwlib_views_path', '');

		if (empty($views_path)) {
			throw new InvalidView('Views path not set.');
		}

		if (!preg_match('/\.php/', $view)) {
			$view .= '.php';
		}

		$file_path = trailingslashit($views_path) . $view;

		if (is_readable($file_path)) {
			extract($this->data);
			include $file_path;
		} else {
			throw new InvalidView("Requested template file $file_path not found.");
		}
	}
}