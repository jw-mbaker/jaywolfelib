<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\AdminMenu;

use Invoker\InvokerInterface;

abstract class AbstractMenuPage implements MenuPageInterface
{
	public const SLUG = 'slug';
	public const CALLABLE = 'callable';
	public const PAGE_TITLE = 'page_title';
	public const MENU_TITLE = 'menu_title';
	public const CAPABILITY = 'capability';
	public const POSITION = 'position';
	public const MAP = 'map';

	public const DEFAULTS = [
		self::PAGE_TITLE => '',
		self::MENU_TITLE => '',
		self::CAPABILITY => '',
		self::POSITION => null,
		self::MAP => []
	];

	protected $slug;
	protected $callable;
	protected $id;
	protected $map;

	public function __construct(
		string $slug,
		$callable,
		string $page_title = self::DEFAULTS[self::PAGE_TITLE],
		string $menu_title = self::DEFAULTS[self::MENU_TITLE],
		string $capability = self::DEFAULTS[self::CAPABILITY],
		string $position = self::DEFAULTS[self::POSITION],
		array $map = self::DEFAULTS[self::MAP]
	) {
		if (null !== $position && !is_numeric($position)) {
			throw new \UnexpectedValueException('$position must be a numeric value!');
		}

		$this->slug = $slug;
		$this->callable = $callable;
		$this->capability = $capability;
		$this->position = $position;
		$this->map = $map;
	}

	public function id(): MenuId
	{
		return $this->id ??= MenuId::fromMenuPage($this);
	}

	public function slug(): string
	{
		return $this->slug;
	}

	public function capability(): string
	{
		return $this->capability;
	}

	public function position()
	{
		return $this->position;
	}

	public function map(): array
	{
		return $this->map;
	}

	public function __invoke(InvokerInterface $invoker, ...$arguments)
	{
		return $invoker->call($this->callable, ...$arguments);
	}
}