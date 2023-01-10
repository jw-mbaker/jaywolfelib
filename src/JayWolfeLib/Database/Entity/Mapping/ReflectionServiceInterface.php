<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Entity\Mapping;

interface ReflectionServiceInterface
{
	public function getParentClasses(string $class): array;
	public function getClassShortName(string $class): string;
	public function getClassNamespace(string $class): string;
	public function getClass(string $class): \ReflectionClass;
	public function getAccessibleProperty(string $class, string $property): \ReflectionProperty;
	public function hasPublicMethod(string $class, string $method): bool;
}