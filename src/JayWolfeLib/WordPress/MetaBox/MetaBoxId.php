<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\MetaBox;

use JayWolfeLib\ObjectHash\AbstractObjectHash;

class MetaBoxId extends AbstractObjectHash
{
	public static function fromMetaBox(MetaBoxInterface $meta_box): self
	{
		return new self($meta_box);
	}
}