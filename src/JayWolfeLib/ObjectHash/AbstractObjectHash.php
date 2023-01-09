<?php declare(strict_types=1);

namespace JayWolfeLib\ObjectHash;

abstract class AbstractObjectHash
{
	protected string $id;

	public function __construct(object $obj)
	{
		$this->id = spl_object_hash($obj);
	}

	public function __toString()
	{
		return $this->id;
	}
}