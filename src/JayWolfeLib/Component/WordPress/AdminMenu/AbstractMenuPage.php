<?php

namespace JayWolfeLib\Component\WordPress\AdminMenu;

use JayWolfeLib\Component\ObjectHash\ObjectHashTrait;
use JayWolfeLib\Traits\SettingsTrait;
use Invoker\InvokerInterface;

abstract class AbstractMenuPage implements MenuPageInterface
{
	use SettingsTrait;
	use ObjectHashTrait;

	protected $slug;
	protected $callable;

	public function __construct(Slug $slug, $callable, array $settings = [])
	{
		$this->slug = $slug;
		$this->callable = $settings['callable'] = $callable;

		$settings['map'] ??= [];
		$this->settings = array_merge(static::DEFAULTS, $settings);
	}

	public function id(): MenuId
	{
		return $this->id ??= MenuId::fromMenuPage($this);
	}

	public function slug(): Slug
	{
		return $this->slug;
	}

	public function __invoke(InvokerInterface $invoker, ...$arguments)
	{
		return $invoker->call($this->callable, ...$arguments);
	}
}