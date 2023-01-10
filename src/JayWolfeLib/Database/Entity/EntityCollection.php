<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Entity;

use JayWolfeLib\Collection\AbstractCollection;

class EntityCollection extends AbstractCollection
{
	/**
	 * The array of entities.
	 *
	 * @var array<string, EntityInterface>
	 */
	private array $entities = [];

	public function all(): array
	{
		return $this->entities;
	}
}