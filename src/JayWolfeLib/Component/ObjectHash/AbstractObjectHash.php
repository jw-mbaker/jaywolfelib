<?php

namespace JayWolfeLib\Component\ObjectHash;

abstract class AbstractObjectHash
{
	protected $id;

	public function __construct(object $obj)
	{
		$this->id = spl_object_hash($obj);
	}
}