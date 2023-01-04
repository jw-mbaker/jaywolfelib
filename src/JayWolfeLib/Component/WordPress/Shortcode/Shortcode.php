<?php

namespace JayWolfeLib\Component\WordPress\Shortcode;

use JayWolfeLib\Component\ObjectHash\ObjectHashTrait;
use JayWolfeLib\Traits\SettingsTrait;
use Invoker\InvokerInterface;

class Shortcode implements ShortcodeInterface
{
	use SettingsTrait;
	use ObjectHashTrait;

	public const TYPE = 'shortcode';

	protected $tag;
	protected $callable;

	public function __construct(string $tag, $callable, array $settings = [])
	{
		$this->tag = $tag;
		$this->callable = $settings['callable'] = $callable;

		$settings['map'] ??= [];
		$this->settings = $settings;

		$this->set_id_from_type(static::TYPE);
	}

	public function tag(): string
	{
		return $this->tag;
	}

	public function __invoke(InvokerInterface $invoker, ...$arguments)
	{
		return $invoker->call($this->callable, $arguments);
	}
}