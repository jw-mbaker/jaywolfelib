<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\Shortcode;

use JayWolfeLib\Entity\AbstractObjectHash;

class ShortcodeId extends AbstractObjectHash
{
	public static function fromShortcode(ShortcodeInterface $shortcode): self
	{
		return new self($shortcode);
	}
}