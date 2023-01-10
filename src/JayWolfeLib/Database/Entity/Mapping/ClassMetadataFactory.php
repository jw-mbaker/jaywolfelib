<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Entity\Mapping;

use ReflectionClass;

class ClassMetadataFactory
{
	/**
	 * @var array<string, ClassMetadata>
	 */
	private array $loadedMetadata = [];

	private ReflectionServiceInterface $reflectionService;

	/**
	 * @return array<string, ClassMetadata>
	 */
	public function getLoadedMetadata(): array
	{
		return $this->loadedMetadata;
	}

	public function getMetadataFor(string $className): ClassMetadata
	{
		$className = $this->normalizeClassName($className);

		if (isset($this->loadedMetadata[$className])) {
			return $this->loadedMetadata[$className];
		}

		if (class_exists($className, false) && (new ReflectionClass($className))->isAnonymous()) {
			throw new \InvalidArgumentException("$className is anonymous.");
		}

		if (!class_exists($className, false)) {
			throw new \InvalidArgumentException("$className not found.");
		}

		try {
			$this->loadMetadata($className);
		} catch (\Exception $e) {

		}
	}

	public function setReflectionService(ReflectionServiceInterface $reflectionService)
	{
		$this->reflectionService = $reflectionService;
	}

	public function getReflectionService(): ReflectionServiceInterface
	{
		return $this->reflectionService ??= new ReflectionService();
	}

	protected function loadMetadata(string $name)
	{
		$loaded = [];

		$parentClasses = $this->getParentClasses($name);
		$parentClasses[] = $name;

		$parent = null;
		$rootEntityFound = false;
		$visited = [];
		$reflectionService = $this->getReflectionService();

		foreach ($parentClasses as $className) {
			if (isset($this->loadedMetadata[$className])) {
				$parent = $this->loadedMetadata[$className];

				if ($this->isEntity($parent)) {
					$rootEntityFound = true;

					array_unshift($visited, $className);
				}

				continue;
			}

			$class = $this->newClassMetadataInstance($className);
			$this->initializeReflection($class, $reflectionService);
		}
	}

	protected function newClassMetadataInstance(string $className): ClassMetadataInterface
	{
		return new ClassMetadata(
			$className,
			$this->em->getNamingStrategy(),
			$this->em->getTypedFieldMapper()
		);
	}

	/**
	 * @return string[]
	 */
	protected function getParentClasses(string $name): array
	{
		$parentClasses = [];

		foreach (array_reverse($this->getReflectionService()->getParentClasses($name)) as $parentClass) {
			$parentClasses[] = $parentClass;
		}

		return $parentClasses;
	}

	protected function initializeReflection(ClassMetadataInterface $class, ReflectionServiceInterface $reflectionService)
	{
		assert($class instanceof ClassMetadataInterface);
		$class->initializeReflection($reflectionService);
	}

	protected function isEntity(ClassMetadataInterface $class): bool
	{
		return !$class->isMappedSuperClass;
	}

	private function normalizeClassName(string $className): string
	{
		return ltrim($className, '\\');
	}
}