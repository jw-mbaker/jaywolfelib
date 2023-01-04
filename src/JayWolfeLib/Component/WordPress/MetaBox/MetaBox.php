<?php

namespace JayWolfeLib\Component\WordPress\MetaBox;

use JayWolfeLib\Component\ObjectHash\AbstractObjectHash;
use JayWolfeLib\Traits\SettingsTrait;
use Invoker\InvokerInterface;

class MetaBox extends AbstractObjectHash implements MetaBoxInterface
{
	use SettingsTrait;

	public const TYPE = 'meta_box';

	protected $meta_id;
	protected $title;
	protected $callable;

	public function __construct(string $meta_id, string $title, $callable, array $settings = [])
	{
		$this->meta_id = $meta_id;
		$this->title = $title;
		$this->callable = $settings['callable'] = $callable;

		$settings['screen'] ??= null;
		$settings['context'] ??= 'advanced';
		$settings['priority'] ??= 'default';
		$settings['callback_args'] ??= null;
		$settings['map'] ??= [];

		$this->settings = $settings;

		$this->set_id_from_type(static::TYPE);
	}

	public function meta_id(): string
	{
		return $this->meta_id;
	}

	public function title(): string
	{
		return $this->title;
	}

	public function __invoke(InvokerInterface $invoker, ...$arguments)
	{
		return $invoker->call($this->callable, $arguments);
	}
}