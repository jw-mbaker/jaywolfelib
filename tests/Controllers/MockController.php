<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Controllers;

use JayWolfeLib\Controllers\AbstractController;
use JayWolfeLib\Config\ConfigInterface;

class MockController extends AbstractController
{
	public function setConfig(ConfigInterface $config)
	{
		$this->config = $config;
	}

	public function __call(string $name, array $arguments)
	{
		return call_user_func_array([$this, $name], $arguments);
	}
}