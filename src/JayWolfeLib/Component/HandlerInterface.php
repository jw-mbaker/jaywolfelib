<?php

namespace JayWolfeLib\Component\WordPress;

use Invoker\InvokerInterface;

interface HandlerInterface
{
	public function __invoke(InvokerInterface $invoker, array $arguments);
}