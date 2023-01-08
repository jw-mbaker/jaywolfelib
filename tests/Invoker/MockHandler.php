<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Invoker;

use JayWolfeLib\Invoker\HandlerInterface;
use Invoker\InvokerInterface;

class MockHandler implements HandlerInterface
{
	public const DEFAULTS = [
		self::MAP => []
	];

	private $callable;
	private array $map;

	public function __construct($callable, array $map = [])
	{
		$this->callable = $callable;
		$this->map = $map;
	}

	public function callable()
	{
		return $this->callable;
	}

	public function map(): array
	{
		return $this->map;
	}

	public function __invoke(InvokerInterface $invoker, ...$args)
	{
		return $invoker->call($this->callable, $args);
	}

	public static function create(array $args): self
	{
		$args = array_merge(self::DEFAULTS, $args);

		return new self(
			$args[self::CALLABLE],
			$args[self::MAP]
		);
	}
}