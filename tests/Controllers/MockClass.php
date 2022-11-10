<?php

namespace JayWolfeLib\Tests\Controllers;

use JayWolfeLib\Controllers\Controller;

class MockClass extends Controller
{
	public $val;

	public function init(): void
	{
		$this->val = 1;
	}
}