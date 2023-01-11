<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\Filter;

use JayWolfeLib\Common\ObjectHash\AbstractObjectHash;

class HookId extends AbstractObjectHash
{
	public static function fromHook(HookInterface $hook): self
	{
		return new self($hook);
	}
}