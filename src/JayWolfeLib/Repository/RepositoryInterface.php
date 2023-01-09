<?php declare(strict_types=1);

namespace JayWolfeLib\Repository;

use JayWolfeLib\Entity\EntityInterface;

interface RepositoryInterface
{
	public function add(EntityInterface $entity);
	public function update(EntityInterface $entity);
	public function remove(EntityInterface $entity);
}