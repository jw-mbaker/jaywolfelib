<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\Shortcode;

use JayWolfeLib\Common\ObjectHash\AbstractObjectHash;

class ShortcodeId extends AbstractObjectHash
{
	public static function fromShortcode(ShortcodeInterface $shortcode): self
	{
		return new self($shortcode);
	}
}