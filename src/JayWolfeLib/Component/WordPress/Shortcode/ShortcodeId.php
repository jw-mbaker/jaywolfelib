<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\Shortcode;

use JayWolfeLib\ObjectHash\AbstractObjectHash;

class ShortcodeId extends AbstractObjectHash
{
	public static function fromShortcode(ShortcodeInterface $shortcode): self
	{
		return new self($shortcode);
	}
}