<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Repository;

use JayWolfeLib\Database\Entity\EntityManager;

final class RepositoryFactory
{
	/**
	 * @var array<string, RepositoryInterface>
	 */
	private array $repositoryList = [];

	public function getRepository(EntityManager $em, string $entityName): RepositoryInterface
	{
		
	}

	private function createRepository()
	{

	}
}