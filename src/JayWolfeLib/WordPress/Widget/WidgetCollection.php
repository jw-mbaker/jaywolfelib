<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\Widget;

use JayWolfeLib\Common\Collection\AbstractCollection;
use WP_Widget;

class WidgetCollection extends AbstractCollection
{
	/**
	 * @var array<string, WidgetInterface>
	 */
	private array $widgets = [];

	private function add(WidgetInterface $widget)
	{
		$this->widgets[(string) $widget->id()] = $widget;
	}

	public function registerWidget(WidgetInterface $widget)
	{
		$this->add($widget);
		\register_widget($widget->wpWidget());
	}

	public function unregisterWidget($wpWidget): bool
	{
		$widget = $this->get($wpWidget);

		if (null !== $widget) {
			$this->remove($widget);
			return true;
		}

		return false;
	}

	public function all(): array
	{
		return $this->widgets;
	}

	public function getById(WidgetId $id): ?WidgetInterface
	{
		return $this->widgets[(string) $id] ?? null;
	}

	/**
	 * Retrieve the widget object by the WP_Widget.
	 *
	 * @param string|WP_Widget $wpWidget
	 * @return WidgetInterface|null
	 */
	public function get($wpWidget): ?WidgetInterface
	{
		$widget = array_reduce($this->widgets, function($carry, $item) use ($wpWidget) {
			if (null !== $carry) return $carry;

			return $item->wpWidget() === $wpWidget ? $item : null;
		}, null);

		return $widget;
	}

	private function remove(WidgetInterface $widget)
	{
		\unregister_widget($widget->wpWidget());
		unset($this->widgets[(string) $widget->id()]);
	}
}