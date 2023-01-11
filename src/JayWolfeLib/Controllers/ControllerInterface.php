<?php declare(strict_types=1);

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
}