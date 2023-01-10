<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Entity\Mapping;

class NamingStrategy
{
	public function classToTableName(string $className): string
	{
		if (str_contains($className, '\\')) {
			return substr($className, strrpos($className, '\\') + 1);
		}

		return $className;
	}

	public function propertyToColumnName(string $propertyName, ?string $className = null): string
	{
		return $propertyName;
	}

	public function referenceColumnName(): string
	{
		return 'id';
	}

	public function joinColumnName(string $propertyName, ?string $className = null)
	{
		return $propertyName . '_' . $this->referenceColumnName();
	}

	public function joinTableName(string $sourceEntity, string $targetEntity, ?string $propertyName = null): string
	{
		return strtolower($this->classToTableName($sourceEntity) . '_' . $this->classToTableName($targetEntity));
	}

	public function joinKeyColumnName(string $entityName, ?string $referencedColumnName = null): string
	{
		return strtolower($this->classToTableName($entityName) . '_' . ($referencedColumnName ?: $this->referenceColumnName()));
	}
}