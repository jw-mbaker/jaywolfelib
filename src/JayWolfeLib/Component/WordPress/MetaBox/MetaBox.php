<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\MetaBox;

use JayWolfeLib\Invoker\CallableTrait;
use Invoker\InvokerInterface;
use InvalidArgumentException;
use WP_Screen;

class MetaBox implements MetaBoxInterface
{
	use CallableTrait;

	public const DEFAULTS = [
		self::SCREEN => null,
		self::CONTEXT => 'advanced',
		self::PRIORITY => 'default',
		self::CALLBACK_ARGS => null,
		self::MAP => []
	];

	protected MetaBoxId $id;
	protected string $meta_id;
	protected string $title;

	/**
	 * The screen or screens on which to show the box.
	 *
	 * @var string|array|WP_Screen
	 */
	protected $screen;

	protected string $context;
	protected string $priority;
	protected ?array $callback_args = null;

	/**
	 * Constructor.
	 *
	 * @param string $meta_id
	 * @param string $title
	 * @param mixed $callable
	 * @param string|array|WP_Screen|null $screen
	 * @param string $context
	 * @param string $pirority
	 * @param array|null $callback_args
	 * @param array $map
	 */
	public function __construct(
		string $meta_id,
		string $title,
		$callable,
		$screen = self::DEFAULTS[self::SCREEN],
		string $context = self::DEFAULTS[self::CONTEXT],
		string $pirority = self::DEFAULTS[self::PRIORITY],
		array $callback_args = self::DEFAULTS[self::CALLBACK_ARGS],
		array $map = self::DEFAULTS[self::MAP]
	) {
		self::validate_screen($screen);

		$this->meta_id = $meta_id;
		$this->title = $title;
		$this->callable = $callable;
		$this->screen = $screen;
		$this->context = $context;
		$this->priority = $pirority;
		$this->callback_args = $callback_args;
		$this->map = $map;
	}

	public function id(): MetaBoxId
	{
		return $this->id ??= MetaBoxId::fromMetaBox($this);
	}

	public function meta_id(): string
	{
		return $this->meta_id;
	}

	public function title(): string
	{
		return $this->title;
	}

	/**
	 * @return string|array|WP_Screen|null
	 */
	public function screen()
	{
		return $this->screen;
	}

	public function context(): string
	{
		return $this->context;
	}

	public function priority(): string
	{
		return $this->priority;
	}

	public function callback_args(): ?array
	{
		return $this->callback_args;
	}

	public function __invoke(InvokerInterface $invoker, ...$arguments)
	{
		return $invoker->call($this->callable, $arguments);
	}

	public static function create(array $args): MetaBoxInterface
	{
		$args = array_merge(self::DEFAULTS, $args);

		return new self(
			$args[self::META_ID],
			$args[self::TITLE],
			$args[self::CALLABLE],
			$args[self::SCREEN],
			$args[self::CONTEXT],
			$args[self::PRIORITY],
			$args[self::CALLBACK_ARGS],
			$args[self::MAP]
		);
	}

	/**
	 * Validate the $screen value.
	 *
	 * @param mixed $screen
	 * @throws InvalidArgumentException
	 */
	protected static function validate_screen($screen)
	{
		if (null !== $screen && !is_string($screen) && !is_array($screen) && !$screen instanceof WP_Screen) {
			throw new InvalidArgumentException('Invalid value passed to $screen.');
		}
	}
}