<?php

namespace JayWolfeLib\Component\WordPress\AdminMenu;

use JayWolfeLib\Traits\SettingsTrait;
use Symfony\Component\HttpFoundation\Response;
use Invoker\InvokerInterface;

trait MenuPageTrait
{
	use SettingsTrait;

	protected $id = '';
	protected $slug = '';
	protected $callable;

	public function id(): string
	{
		return $this->id;
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