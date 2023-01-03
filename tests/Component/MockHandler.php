<?php

namespace JayWolfeLib\Tests\Component;

use JayWolfeLib\Component\HandlerInterface;
use JayWolfeLib\Traits\SettingsTrait;
use Invoker\InvokerInterface;

class MockHandler implements HandlerInterface
{
	use SettingsTrait;

	private $callable;

	public function __construct($callable, array $settings = [])
	{
		$this->callable = $settings['callable'] = $callable;
		$settings['map'] ??= [];
		$this->settings = $settings;
	}

	public function callable()
	{
		return $this->callable;
	}

	public function __invoke(InvokerInterface $invoker, ...$args)
	{
		return $invoker->call($this->callable, $args);
	}
}