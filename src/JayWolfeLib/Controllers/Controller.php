<?php

namespace JayWolfeLib\Controllers;

use JayWolfeLib\Container;
use JayWolfeLib\Input;
use JayWolfeLib\Config\ConfigInterface;
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

	/**
	 * The config.
	 *
	 * @var ConfigInterface
	 */
	protected $config;

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
	 * @param ConfigInterface $config
	 */
	public function __construct(Input $input, ModelFactoryInterface $models, ConfigInterface $config)
	{
		$this->input = $input;
		$this->models = $models;
		$this->config = $config;
	}

	/**
	 * Render a view from the path specified
	 * in the views_path config setting.
	 *
	 * @param string $view
	 * @return void
	 * @throws InvalidView
	 */
	public function render(string $view)
	{
		if (null === $this->config->get('views_path')) {
			throw new InvalidView("Views path not set for " . plugin_basename($this->config->get('plugin_file')) . ".");
		}

		$views_path = $this->config->get('views_path');

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