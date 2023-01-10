<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Entity\Mapping;

use ReflectionClass;
use ReflectionProperty;
use ReflectionMethod;

class ReflectionService implements ReflectionServiceInterface
{
	public function getParentClasses(string $class): array
	{
		if (!class_exists($class)) {
			throw new \BadMethodCallException("$class not found.");
		}

		$parents = class_parents($class);

		assert($parents !== false);

		return $parents;
	}

	public function getClassShortName(string $class): string
	{
		return $this->getClass($class)->getShortName();
	}

	public function getClassNamespace(string $class): string
	{
		return $this->getClass($class)->getNamespaceName();
	}

	public function getClass(string $class): ReflectionClass
	{
		return new ReflectionClass($class);
	}

	public function getAccessibleProperty(string $class, string $property): ReflectionProperty
	{
		$reflectionProperty = new ReflectionProperty($class, $property);

		$reflectionProperty->setAccessible(true);

		return $reflectionProperty;
	}

	public function hasPublicMethod(string $class, string $method): bool
	{
		try {
			$reflectionMethod = new ReflectionMethod($class, $method);
		} catch (\ReflectionException $e) {
			return false;
		}

		return $reflectionMethod->isPublic();
	}
}