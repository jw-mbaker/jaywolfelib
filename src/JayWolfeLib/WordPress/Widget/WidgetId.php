<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\Widget;

use JayWolfeLib\Common\ObjectHash\AbstractObjectHash;

class WidgetId extends AbstractObjectHash
{
	public static function fromWidget(WidgetInterface $widget): self
	{
		return new self($widget);
	}
}