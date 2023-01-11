<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\Filter;

use JayWolfeLib\Invoker\CallableTrait;
use Invoker\InvokerInterface;

abstract class AbstractHook implements HookInterface
{
	use CallableTrait;

	public const DEFAULTS = [
		self::PRIORITY => 10,
		self::NUM_ARGS => 1,
		self::MAP => []
	];

	protected HookId $id;
	protected string $hook;
	protected int $priority;
	protected int $numArgs;

	public function __construct(
		string $hook,
		$callable,
		int $priority = self::DEFAULTS[self::PRIORITY],
		int $numArgs = self::DEFAULTS[self::NUM_ARGS],
		array $map = self::DEFAULTS[self::MAP]
	) {
		$this->hook = $hook;
		$this->callable = $callable;
		$this->priority = $priority;
		$this->numArgs = $numArgs;
		$this->map = $map;
	}

	public function id(): HookId
	{
		return $this->id ??= HookId::fromHook($this);
	}

	public function hook(): string
	{
		return $this->hook;
	}

	public function priority(): int
	{
		return $this->priority;
	}

	public function numArgs(): int
	{
		return $this->numArgs;
	}

	public function __invoke(InvokerInterface $invoker, ...$args)
	{
		return $invoker->call($this->callable, $args);
	}

	public static function create(array $args): HookInterface
	{
		$args = array_merge(self::DEFAULTS, static::DEFAULTS, $args);

		return new static(
			$args[self::HOOK],
			$args[self::CALLABLE],
			$args[static::PRIORITY],
			$args[static::NUM_ARGS],
			$args[static::MAP]
		);
	}
}