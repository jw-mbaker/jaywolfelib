<?php

namespace JayWolfeLib\Component\WordPress\Widget;

use JayWolfeLib\Collection\AbstractCollection;

class WidgetCollection extends AbstractCollection
{
	/**
	 * @var array<string, WidgetInterface>
	 */
	private $widgets = [];

	public function add(string $name, WidgetInterface $widget)
	{
		$this->widgets[$name] = $widget;
	}

	public function register_widget(WidgetInterface $widget)
	{
		$this->add($widget->id(), $widget);
		\register_widget($widget->widget());
	}

	public function all(): array
	{
		return $this->widgets;
	}

	public function get(string $name): ?WidgetInterface
	{
		return $this->widgets[$name] ?? null;
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