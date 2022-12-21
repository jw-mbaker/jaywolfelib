<?php

namespace JayWolfeLib\Component\Routing;

use JayWolfeLib\Parameter\ParameterInterface;

interface RouteInterface extends ParameterInterface
{
	public function id(): string;
	public function action(): string;
}