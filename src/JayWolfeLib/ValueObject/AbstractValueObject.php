<?php declare(strict_types=1);

namespace JayWolfeLib\Component\ValueObject;

abstract class AbstractValueObject
{
	public static function fromString(string $val): self
	{
		return new static($val);
	}

	public static function fromInt(int $val): self
	{
		return new static($val);
	}

	public static function fromFloat(float $val): self
	{
		return new static($val);
	}
}