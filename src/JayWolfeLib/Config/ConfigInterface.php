<?php

namespace JayWolfeLib\Config;

use JayWolfeLib\Parameter\ParameterInterface;

interface ConfigInterface extends ParameterInterface
{
	public function get_settings(): array;
	public function requirements_met(): bool;
	public function get_errors(): array;
}