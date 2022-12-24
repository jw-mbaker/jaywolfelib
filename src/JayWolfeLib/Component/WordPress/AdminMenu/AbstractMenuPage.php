<?php

namespace JayWolfeLib\Component\WordPress\AdminMenu;

use JayWolfeLib\Component\ObjectHash\AbstractObjectHash;
use JayWolfeLib\Traits\SettingsTrait;
use Symfony\Component\HttpFoundation\Response;
use Invoker\InvokerInterface;

abstract class AbstractMenuPage extends AbstractObjectHash implements MenuPageInterface
{
	use SettingsTrait;

	protected $slug = '';
	protected $callable;

	public function __construct(string $slug, $callable, array $settings = [])
	{
		$this->slug = $slug;
		$this->callable = $settings['callable'] = $callable;

		$this->settings = array_merge(static::DEFAULTS, $settings);

		$this->id ??= $this->set_id_from_type(static::MENU_TYPE);
	}

	public function slug(): string
	{
		return $this->slug;
	}

	public function __invoke(InvokerInterface $invoker, array $arguments)
	{
		$response = $invoker->call($this->callable, $arguments);

		if ($response instanceof Response) {
			$response->send();
		}
	}
}