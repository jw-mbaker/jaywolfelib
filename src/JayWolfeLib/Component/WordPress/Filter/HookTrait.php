<?php

namespace JayWolfeLib\Component\Routing;

use JayWolfeLib\Traits\SettingsTrait;

trait HookTrait
{
	use SettingsTrait;

	protected $id = '';
	protected $hook = '';
	protected $callable;

	public function id(): string
	{
		return $this->id;
	}

	public function hook(): string
	{
		return $this->hook;
	}
}