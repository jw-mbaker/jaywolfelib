<?php declare(strict_types=1);

namespace JayWolfeLib\Invoker;

use Invoker\InvokerInterface;

interface HandlerInterface
{
	public const CALLABLE = 'callable';
	public const MAP = 'map';

	public function __invoke(InvokerInterface $invoker, ...$args);
	public function map(): array;
}
