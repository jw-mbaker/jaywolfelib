<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\Shortcode;

use JayWolfeLib\Invoker\HandlerInterface;

interface ShortcodeInterface extends HandlerInterface
{
	public const TAG = 'tag';

	public function tag(): string;
}