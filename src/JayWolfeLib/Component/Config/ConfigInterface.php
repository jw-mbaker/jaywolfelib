<?php declare(strict_types=1);

namespace JayWolfeLib\Component\Config;

interface ConfigInterface
{
	public function requirements_met(): bool;
	public function get_errors(): array;
}