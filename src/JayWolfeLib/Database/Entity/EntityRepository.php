<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Entity;

use JayWolfeLib\Database\Repository\RepositoryInterface;
use JayWolfeLib\Database\Query\QueryBuilder;
use JayWolfeLib\Database\Entity\Mapping\ClassMetadataInterface;
use wpdb;

class EntityRepository implements RepositoryInterface
{
	protected $entityName;
	protected EntityManager $em;
	protected ClassMetadataInterface $class;

	public function __construct(EntityManager $em, ClassMetadataInterface $class)
	{
		$this->entityName = $class->name;
		$this->em = $em;
		$this->class = $class;
	}

	public function query(): QueryBuilder
	{
		
	}

	public function getClassName(): string
	{
		return $this->entity;
	}
}