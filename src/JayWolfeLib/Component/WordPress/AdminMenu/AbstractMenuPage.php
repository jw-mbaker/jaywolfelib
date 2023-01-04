<?php

namespace JayWolfeLib\Component\WordPress\AdminMenu;

use JayWolfeLib\Component\ObjectHash\ObjectHashTrait;
use JayWolfeLib\Traits\SettingsTrait;
use Invoker\InvokerInterface;

abstract class AbstractMenuPage implements MenuPageInterface
{
	use SettingsTrait;
	use ObjectHashTrait;

	protected $slug = '';
	protected $callable;

	public function __construct(string $slug, $callable, array $settings = [])
	{
		$this->slug = $slug;
		$this->callable = $settings['callable'] = $callable;

		$settings['map'] ??= [];
		$this->settings = array_merge(static::DEFAULTS, $settings);

		$this->set_id_from_type(static::MENU_TYPE);
	}

	public function slug(): string
	{
		return $this->slug;
	}

	public function __invoke(InvokerInterface $invoker, ...$arguments)
	{
		return $invoker->call($this->callable, ...$arguments);
	}
}