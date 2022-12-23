<?php

namespace JayWolfeLib\Component;

use Invoker\InvokerInterface;

interface HandlerInterface
{
	public function __invoke(InvokerInterface $invoker, array $arguments);
}
