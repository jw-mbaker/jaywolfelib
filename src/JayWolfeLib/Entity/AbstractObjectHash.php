<?php declare(strict_types=1);

namespace JayWolfeLib\Entity;

abstract class AbstractObjectHash implements EntityInterface
{
	protected string $id;

	public function __construct(object $obj)
	{
		$this->set_id(spl_object_hash($obj));
	}

	public function set_id(string $id)
	{
		$this->id = $id;
	}

	public function get_id(): string
	{
		return $this->id;
	}

	public function __toString()
	{
		return $this->get_id();
	}
}