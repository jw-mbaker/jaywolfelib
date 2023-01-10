<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Entity;

use JayWolfeLib\Annotation\Manager as AnnotationManager;
use JayWolfeLib\Database\Entity\Mapping\NamingStrategy;
use JayWolfeLib\Database\Entity\Mapping\TypedFieldMapper;
use JayWolfeLib\Database\Annotation\Entity;
use JayWolfeLib\Database\Query\QueryBuilder;
use JayWolfeLib\Traits\ContainerAwareTrait;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class EntityManager
{
	use ContainerAwareTrait;

	private AnnotationManager $annotationManager;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getRepository(string $class): RepositoryInterface
	{
		if (!class_exists($class)) {
			throw new \InvalidArgumentException("$class does not exist.");
		}

		$reflection = new ReflectionClass($class);
		$annot = $this->getAnnotationManager()->getAnnotationReader()->getClassAnnotation($reflection, Entity::class);
		if (!$annot instanceof Entity) {
			throw new \BadMethodCallException(
				sprintf('Annotation %s not found for %s.', Entity::class, $class)
			);
		}

		$repo = $this->container->get($annot->repositoryClass);
		
		if (!$repo instanceof RepositoryInterface) {
			throw new \BadMethodCallException(
				sprintf('%s must implement %s.', get_class($repo), RepositoryInterface::class)
			);
		}

		return $repo;
	}

	public function createQueryBuilder(): QueryBuilder
	{
		return new QueryBuilder($this);
	}

	public function getNamingStrategy(): NamingStrategy
	{
		return $this->container->get(NamingStrategy::class);
	}

	public function getTypedFieldMapper(): TypedFieldMapper
	{
		return $this->container->get(TypedFieldMapper::class);
	}

	private function getAnnotationManager(): AnnotationManager
	{
		if (null === $this->annotationManager) {
			$this->annotationManager = $this->container->get(AnnotationManager::class);
			$this->annotationManager->getAnnotationReader()->addNamespace("JayWolfeLib\\Database\\Annotation");
		}

		return $this->annotationManager;
	}
}