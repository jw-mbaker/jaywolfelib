<?php

namespace JayWolfeLib\Component\WordPress\Widget;

use JayWolfeLib\Collection\AbstractCollection;
use WP_Widget;

class WidgetCollection extends AbstractCollection
{
	/**
	 * @var array<string, WidgetInterface>
	 */
	private $widgets = [];

	private function add(string $name, WidgetInterface $widget)
	{
		$this->widgets[$name] = $widget;
	}

	public function register_widget(WidgetInterface $widget)
	{
		$this->add($widget->id(), $widget);
		\register_widget($widget->widget());
	}

	public function unregister_widget($wp_widget): bool
	{
		$widget = $this->get_by_wp_widget($wp_widget);

		if (null !== $widget) {
			$this->remove($widget->id());
			return true;
		}

		return false;
	}

	public function all(): array
	{
		return $this->widgets;
	}

	public function get(string $name): ?WidgetInterface
	{
		return $this->widgets[$name] ?? null;
	}

	/**
	 * Retrieve the widget object by the WP_Widget.
	 *
	 * @param string|WP_Widget $wp_widget
	 * @return WidgetInterface|null
	 */
	public function get_by_wp_widget($wp_widget): ?WidgetInterface
	{
		$widget = array_reduce($this->widgets, function($carry, $item) use ($wp_widget) {
			if (null !== $carry) return $carry;

			return $item->widget() === $wp_widget ? $item : null;
		}, null);

		return $widget;
	}

	public function remove($name)
	{
		foreach ((array) $name as $n) {
			$widget = $this->widgets[$n];

			\unregister_widget($widget->widget());
			unset($this->widgets[$n]);
		}
	}
}