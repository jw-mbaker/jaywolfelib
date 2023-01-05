<?php

namespace JayWolfeLib\Tests\Controllers;

use JayWolfeLib\Controllers\AbstractController;

class MockController extends AbstractController
{
	public function init()
	{
		
	}

	public function __call(string $name, array $arguments)
	{
		return call_user_func_array([$this, $name], $arguments);
	}
}