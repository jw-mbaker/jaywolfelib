<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Repository;

use JayWolfeLib\Database\QueryBuilder;
use JayWolfeLib\Database\Entity\EntityInterface;
use wpdb;

use function JayWolfeLib\snake_case;

abstract class AbstractRepository implements RepositoryInterface
{
	protected wpdb $wpdb;

	protected string $table;

	public function __construct(wpdb $wpdb)
	{
		$this->wpdb = $wpdb;
	}

	public function getTable(): string
	{
		if (!$this->table) {
			$table = str_replace(['repository'], '', strtolower(static::class));
			$this->table = snake_case($table);
		}

		return $this->table;
	}

	public function setTable(string $table)
	{
		$this->table = $table;
	}

	public function query(?QueryBuilder $query = null): QueryBuilder
	{
		return $query ?? new QueryBuilder($this->table);
	}

	public function where(array $where, ?QueryBuilder $query = null): QueryBuilder
	{
		$query ??= $this->query();
		return $query->where($where);
	}

	public function get()
	{

	}

	public function execute(QueryBuilder $builder)
	{
		$sql = $this->wpdb->prepare($builder->toString(), $builder->getArgs());
		$result = $this->wpdb->query($sql);
	}
}