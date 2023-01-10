<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Entity\Mapping;

use ReflectionClass;

class ClassMetadata implements ClassMetadataInterface
{
	public string $name;
	public string $rootEntityName;
	public array $table = [];
	public ReflectionClass $reflectionClass;
	public bool $isMappedSuperClass = false;
	protected NamingStrategy $namingStrategy;
	private TypedFieldMapper $typedFieldMapper;

	public function __construct(
		string $entityName,
		?NamingStrategy $namingStrategy = null,
		?TypedFieldMapper $typedFieldMapper = null
	) {
		$this->name = $entityName;
		$this->rootEntityName = $entityName;
		$this->namingStrategy = $namingStrategy ?? new NamingStrategy();
		$this->typedFieldMapper = $typedFieldMapper ?? new TypedFieldMapper();
	}

	public function initializeReflection(ReflectionServiceInterface $reflectionService)
	{
		$this->reflectionClass = $reflectionService->getClass($this->name);
		$this->namespace = $reflectionService->getClassNamespace($this->name);

		if ($this->reflectionClass) {
			$this->name = $this->rootEntityName = $this->reflectionClass->getName();
		}

		$this->table['name'] = $this->namingStrategy->classToTableName($this->name);
	}
}