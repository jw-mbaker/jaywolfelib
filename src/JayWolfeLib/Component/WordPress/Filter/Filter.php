<?php

namespace JayWolfeLib\Component\WordPress\Filter;

use Invoker\InvokerInterface;

class Filter implements HookInterface
{
	use HookTrait;

	public function __construct(string $hook, $callable, array $settings = [])
	{
		$this->hook = $hook;
		$this->callable = $callable;

		$settings['priority'] ??= 10;
		$settings['num_args'] ??= 1;

		$this->settings = $settings;

		$this->id = 'filter_' . spl_object_hash($this);
	}

	public function __invoke(InvokerInterface $invoker, array $arguments)
	{
		return $invoker->call($this->callable, $arguments);
	}
}