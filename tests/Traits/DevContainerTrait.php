<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Traits;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

trait DevContainerTrait
{
	protected ContainerInterface $container;

	public function createDevContainer(): ContainerInterface
	{
		return ContainerBuilder::buildDevContainer();
	}
}