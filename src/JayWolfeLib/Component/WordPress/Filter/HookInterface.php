<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\Filter;

use JayWolfeLib\Invoker\HandlerInterface;

interface HookInterface extends HandlerInterface
{
	public const HOOK = 'hook';
	public const PRIORITY = 'priority';
	public const NUM_ARGS = 'num_args';

	public function hook(): string;
	public function priority(): int;
	public function num_args(): int;
}