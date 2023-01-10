<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Repository;

use JayWolfeLib\Traits\ContainerAwareTrait;
use Psr\Container\ContainerInterface;
use BadMethodCallException;
use ReflectionClass;

class Manager
{
	use ContainerAwareTrait;

	public function __construct(ContainerInterface $container)
	{
		$this->set_container($container);
	}

	public function get(string $class): RepositoryInterface
	{
		$this->validateClass($class);
		return $this->container->get($class);
	}

	private function validateClass(string $class)
	{
		if (!class_exists($class)) {
			throw new BadMethodCallException("$class not found.");
		}

		$reflection = new ReflectionClass($class);
		if (!$reflection->implementsInterface(RepositoryInterface::class)) {
			throw new BadMethodCallException(
				sprintf('%s does not implement %s.', $class, RepositoryInterface::class)
			);
		}
	}
}