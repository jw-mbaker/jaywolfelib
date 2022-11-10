<?php

namespace JayWolfeLib\Factory;

use JayWolfeLib\Config\ConfigInterface;

interface ConfigFactoryInterface extends BaseFactoryInterface
{
	public function get(string $plugin_file): ConfigInterface;
}