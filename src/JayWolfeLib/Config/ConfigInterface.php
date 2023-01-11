<?php declare(strict_types=1);

namespace JayWolfeLib\Config;

interface ConfigInterface
{
	public function requirementsMet(): bool;
	public function getErrors(): array;
}