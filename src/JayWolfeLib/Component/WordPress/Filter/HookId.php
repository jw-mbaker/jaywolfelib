<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\Filter;

use JayWolfeLib\Entity\AbstractObjectHash;

class HookId extends AbstractObjectHash
{
	public static function fromHook(HookInterface $hook): self
	{
		return new self($hook);
	}
}