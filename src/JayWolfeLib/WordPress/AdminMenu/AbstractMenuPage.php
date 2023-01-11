<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\AdminMenu;

use JayWolfeLib\Invoker\CallableTrait;
use Invoker\InvokerInterface;

abstract class AbstractMenuPage implements MenuPageInterface
{
	use CallableTrait;

	public const DEFAULTS = [
		self::PAGE_TITLE => '',
		self::MENU_TITLE => '',
		self::CAPABILITY => '',
		self::POSITION => null,
		self::MAP => []
	];

	protected string $slug;
	protected string $page_title;
	protected string $menu_title;
	protected string $capability;
	protected MenuId $id;
	protected $position;
	protected array $map;

	public function __construct(
		string $slug,
		$callable,
		string $page_title = self::DEFAULTS[self::PAGE_TITLE],
		string $menu_title = self::DEFAULTS[self::MENU_TITLE],
		string $capability = self::DEFAULTS[self::CAPABILITY],
		$position = self::DEFAULTS[self::POSITION],
		array $map = self::DEFAULTS[self::MAP]
	) {
		if (null !== $position && !is_numeric($position)) {
			throw new \UnexpectedValueException('$position must be a numeric value!');
		}

		$this->slug = $slug;
		$this->callable = $callable;
		$this->page_title = $page_title;
		$this->menu_title = $menu_title;
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

	public function page_title(): string
	{
		return $this->page_title;
	}

	public function menu_title(): string
	{
		return $this->menu_title;
	}

	public function capability(): string
	{
		return $this->capability;
	}

	public function position()
	{
		return $this->position;
	}

	public function __invoke(InvokerInterface $invoker, ...$arguments)
	{
		return $invoker->call($this->callable, ...$arguments);
	}
}