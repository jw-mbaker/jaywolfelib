<?php

namespace JayWolfeLib\Component\ObjectHash;

abstract class AbstractObjectHash implements ObjectHashInterface
{
	protected $id;

	public function id(): string
	{
		return $this->id;
	}

	protected function set_id_from_type(string $type)
	{
		$this->id ??= "{$type}_" . spl_object_hash($this);
	}
}