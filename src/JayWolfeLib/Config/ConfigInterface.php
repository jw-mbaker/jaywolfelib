<?php

namespace JayWolfeLib\Config;

use JayWolfeLib\Parameter\ParameterInterface;

interface ConfigInterface extends ParameterInterface
{
	public function requirements_met(): bool;
	public function get_errors(): array;
}