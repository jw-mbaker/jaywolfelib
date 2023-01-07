<?php

namespace JayWolfeLib\Entity;

abstract class AbstractObjectHash
{
	protected $id;

	public function __construct(object $obj)
	{
		$this->id = spl_object_hash($obj);
	}

	public function __toString()
	{
		return $this->id;
	}
}