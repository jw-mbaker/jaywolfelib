<?php

namespace JayWolfeLib\Controllers;

/**
 * The controller interface.
 * All controllers should implement this interface.
 */
interface ControllerInterface
{
	public function render(string $view_path);
}