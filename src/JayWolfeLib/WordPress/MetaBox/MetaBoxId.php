<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\MetaBox;

use JayWolfeLib\Common\ObjectHash\AbstractObjectHash;

class MetaBoxId extends AbstractObjectHash
{
	public static function fromMetaBox(MetaBoxInterface $metaBox): self
	{
		return new self($metaBox);
	}
}