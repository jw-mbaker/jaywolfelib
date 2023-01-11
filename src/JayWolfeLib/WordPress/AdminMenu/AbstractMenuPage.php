<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\AdminMenu;

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
	protected string $pageTitle;
	protected string $menuTitle;
	protected string $capability;
	protected MenuId $id;
	protected $position;
	protected array $map;

	public function __construct(
		string $slug,
		$callable,
		string $pageTitle = self::DEFAULTS[self::PAGE_TITLE],
		string $menuTitle = self::DEFAULTS[self::MENU_TITLE],
		string $capability = self::DEFAULTS[self::CAPABILITY],
		$position = self::DEFAULTS[self::POSITION],
		array $map = self::DEFAULTS[self::MAP]
	) {
		if (null !== $position && !is_numeric($position)) {
			throw new \UnexpectedValueException('$position must be a numeric value!');
		}

		$this->slug = $slug;
		$this->callable = $callable;
		$this->pageTitle = $pageTitle;
		$this->menuTitle = $menuTitle;
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

	public function pageTitle(): string
	{
		return $this->pageTitle;
	}

	public function menuTitle(): string
	{
		return $this->menuTitle;
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