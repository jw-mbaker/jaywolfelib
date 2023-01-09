<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\Widget;

use JayWolfeLib\Entity\AbstractObjectHash;

class WidgetId extends AbstractObjectHash
{
	public static function fromWidget(WidgetInterface $widget): self
	{
		return new self($widget);
	}
}