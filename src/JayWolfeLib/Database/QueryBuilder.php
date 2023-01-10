<?php declare(strict_types=1);

namespace JayWolfeLib\Database;

use JayWolfeLib\Database\Repository\RepositoryInterface;

class QueryBuilder
{
	private string $table;

	private string $query;
	private array $args;

	public function __construct(string $table)
	{
		$this->table = $table;
	}

	public function getArgs(): array
	{
		return $this->args;
	}

	public function toString(): string
	{
		return $this->query;
	}
}