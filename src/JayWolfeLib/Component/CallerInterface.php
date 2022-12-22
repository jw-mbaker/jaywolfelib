<?php

namespace JayWolfeLib\Component;

interface CallerInterface
{
	public function __call(string $name, array $arguments);
}