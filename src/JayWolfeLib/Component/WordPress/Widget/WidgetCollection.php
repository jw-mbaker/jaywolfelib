<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\Widget;

use JayWolfeLib\Collection\AbstractCollection;
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

	public function register_widget(WidgetInterface $widget)
	{
		$this->add($widget);
		\register_widget($widget->wp_widget());
	}

	public function unregister_widget($wp_widget): bool
	{
		$widget = $this->get($wp_widget);

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

	public function get_by_id(WidgetId $id): ?WidgetInterface
	{
		return $this->widgets[(string) $id] ?? null;
	}

	/**
	 * Retrieve the widget object by the WP_Widget.
	 *
	 * @param string|WP_Widget $wp_widget
	 * @return WidgetInterface|null
	 */
	public function get($wp_widget): ?WidgetInterface
	{
		$widget = array_reduce($this->widgets, function($carry, $item) use ($wp_widget) {
			if (null !== $carry) return $carry;

			return $item->wp_widget() === $wp_widget ? $item : null;
		}, null);

		return $widget;
	}

	private function remove(WidgetInterface $widget)
	{
		\unregister_widget($widget->wp_widget());
		unset($this->widgets[(string) $widget->id()]);
	}
}