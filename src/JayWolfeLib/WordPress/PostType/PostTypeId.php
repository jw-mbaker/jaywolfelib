<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\PostType;

use JayWolfeLib\Common\ObjectHash\AbstractObjectHash;

class PostTypeId extends AbstractObjectHash
{
	public static function fromPostType(PostTypeInterface $postType): self
	{
		return new self($postType);
	}
}