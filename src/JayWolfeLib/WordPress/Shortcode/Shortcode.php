<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\Shortcode;

use JayWolfeLib\Invoker\CallableTrait;
use Invoker\InvokerInterface;

class Shortcode implements ShortcodeInterface
{
	use CallableTrait;

	public const DEFAULTS = [
		self::MAP => []
	];

	protected string $tag;
	protected $callable;

	public function __construct(string $tag, $callable, array $map = self::DEFAULTS[self::MAP])
	{
		$this->tag = $tag;
		$this->callable = $callable;
		$this->map = $map;
	}

	public function tag(): string
	{
		return $this->tag;
	}

	public function id(): ShortcodeId
	{
		return $this->id ??= ShortcodeId::fromShortcode($this);
	}

	public function __invoke(InvokerInterface $invoker, ...$arguments)
	{
		return $invoker->call($this->callable, $arguments);
	}

	public static function create(array $args): self
	{
		$args = array_merge(self::DEFAULTS, $args);

		return new static(
			$args[self::TAG],
			$args[self::CALLABLE],
			$args[self::MAP]
		);
	}
}