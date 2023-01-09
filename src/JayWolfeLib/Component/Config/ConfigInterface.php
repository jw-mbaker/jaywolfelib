<?php declare(strict_types=1);

namespace JayWolfeLib\Component\Config;

use JayWolfeLib\Component\ParameterInterface;

interface ConfigInterface extends ParameterInterface
{
	public function requirements_met(): bool;
	public function get_errors(): array;
}