<?php

namespace JayWolfeLib\Tests\Traits;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

trait DevContainerTrait
{
	protected $container;

	public function createDevContainer(): ContainerInterface
	{
		return ContainerBuilder::buildDevContainer();
	}
}