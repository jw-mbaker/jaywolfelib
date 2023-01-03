<?php

namespace JayWolfeLib\Component\WordPress\Shortcode;

use JayWolfeLib\Component\ObjectHash\AbstractObjectHash;
use JayWolfeLib\Traits\SettingsTrait;
use Invoker\InvokerInterface;

class Shortcode extends AbstractObjectHash implements ShortcodeInterface
{
	use SettingsTrait;

	public const TYPE = 'shortcode';

	protected $tag;
	protected $callable;

	public function __construct(string $tag, $callable, array $settings = [])
	{
		$this->tag = $tag;
		$this->callable = $settings['callable'] = $settings;

		$settings['map'] ??= [];
		$this->settings = $settings;

		$this->id = $this->set_id_from_type(static::TYPE);
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