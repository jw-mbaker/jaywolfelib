<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Invoker;

use JayWolfeLib\Invoker\HandlerInterface;
use JayWolfeLib\Invoker\CallableTrait;
use JayWolfeLib\Common\ObjectHash\AbstractObjectHash;
use Invoker\InvokerInterface;

class MockHandler implements HandlerInterface
{
	use CallableTrait;

	public const NAME = 'name';

	public const DEFAULTS = [
		self::MAP => []
	];

	private $id;
	private string $name;

	public function __construct(string $name, $callable, array $map = [])
	{
		$this->name = $name;
		$this->callable = $callable;
		$this->map = $map;
	}

	public function __invoke(InvokerInterface $invoker, ...$args)
	{
		return $invoker->call($this->callable, $args);
	}

	public function id(): AbstractObjectHash
	{
		return $this->id ??= new class($this) extends AbstractObjectHash {};
	}

	public function name(): string
	{
		return $this->name;
	}

	public static function create(array $args): self
	{
		$args = array_merge(self::DEFAULTS, $args);

		return new self(
			$args[self::NAME],
			$args[self::CALLABLE],
			$args[self::MAP]
		);
	}
}