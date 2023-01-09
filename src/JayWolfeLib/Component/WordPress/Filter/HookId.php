<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\Filter;

use JayWolfeLib\ObjectHash\AbstractObjectHash;

class HookId extends AbstractObjectHash
{
	public static function fromHook(HookInterface $hook): self
	{
		return new self($hook);
	}
}