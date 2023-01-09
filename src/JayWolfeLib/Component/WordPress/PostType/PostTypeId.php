<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\PostType;

use JayWolfeLib\Entity\AbstractObjectHash;

class PostTypeId extends AbstractObjectHash
{
	public static function fromPostType(PostTypeInterface $post_type): self
	{
		return new self($post_type);
	}
}