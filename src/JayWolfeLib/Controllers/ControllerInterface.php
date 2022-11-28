<?php

namespace JayWolfeLib\Controllers;

use JayWolfeLib\Config\ConfigInterface;

/**
 * The controller interface.
 * All controllers should implement this interface.
 */
interface ControllerInterface
{
	/**
	 * Intialize the controller.
	 *
	 * @return mixed
	 */
	public function init();

	public function set_config(ConfigInterface $config);
	public function get_config(): ConfigInterface;
}