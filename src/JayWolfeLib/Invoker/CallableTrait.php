<?php declare(strict_types=1);

namespace JayWolfeLib\Invoker;

trait CallableTrait
{
	protected $callable;
	protected array $map;

	public function map(): array
	{
		return $this->map;
	}

	public function callable()
	{
		return $this->callable;
	}
}