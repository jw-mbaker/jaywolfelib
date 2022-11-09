<?php

namespace JayWolfeLib\Tests\Controllers;

use JayWolfeLib\Controllers\ControllerInterface;

class MockClass implements ControllerInterface
{
	public $val;

	public function init(): void
	{
		$this->val = 1;
	}

	public function render(string $view)
	{

	}
}