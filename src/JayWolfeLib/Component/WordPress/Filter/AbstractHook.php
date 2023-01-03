<?php

namespace JayWolfeLib\Component\WordPress\Filter;

use JayWolfeLib\Component\ObjectHash\AbstractObjectHash;
use JayWolfeLib\Traits\SettingsTrait;
use Invoker\InvokerInterface;

abstract class AbstractHook extends AbstractObjectHash implements HookInterface
{
	use SettingsTrait;

	protected $hook;
	protected $callable;

	public function __construct(string $hook, $callable, array $settings = [])
	{
		$this->hook = $hook;
		$this->callable = $settings['callable'] = $callable;

		$settings['map'] ??= [];
		$settings['priority'] ??= 10;
		$settings['num_args'] ??= 1;

		$this->settings = $settings;

		$this->set_id_from_type(static::HOOK_TYPE);
	}

	public function hook(): string
	{
		return $this->hook;
	}

	public function __invoke(InvokerInterface $invoker, ...$args)
	{
		return $invoker->call($this->callable, $args);
	}
}