<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Repository;

interface RepositoryInterface
{
	public function find($id);

	/**
	 * @return array<int, object>
	 */
	public function findAll(): array;

	/**
	 * @param array<string, mixed> $criteria
	 * @param array<string, string>|null $orderBy
	 * @return array<int, object>
	 */
	public function findBy(
		array $criteria,
		?array $orderBy = null,
		?int $limit = null,
		?int $offset = null
	): array;

	/**
	 * @param array<string, mixed> $criteria
	 * @return object|null
	 */
	public function findByOne(array $criteria);

	/**
	 * Returns the class name of the object managed by the repository.
	 */
	public function getClassName(): string;
}