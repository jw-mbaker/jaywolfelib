<?php declare(strict_types=1);

namespace JayWolfeLib\Invoker;

interface CallerInterface
{
	public function __call(string $name, array $arguments);
}