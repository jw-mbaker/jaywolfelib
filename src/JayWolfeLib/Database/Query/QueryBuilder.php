<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Query;

use JayWolfeLib\Database\Entity\EntityManager;

class QueryBuilder
{
	private EntityManager $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}
}