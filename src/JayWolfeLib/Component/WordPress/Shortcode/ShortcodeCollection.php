<?php

namespace JayWolfeLib\Component\WordPress\Shortcode;

use JayWolfeLib\Collection\AbstractInvokerCollection;

class ShortcodeCollection extends AbstractInvokerCollection
{
	/**
	 * @var array<string, ShortcodeInterface>
	 */
	private $shortcodes = [];

	private function add(string $name, ShortcodeInterface $shortcode)
	{
		$this->shortcodes[$name] = $shortcode;
	}

	/**
	 * Add a shortcode.
	 *
	 * @param ShortcodeInterface $shortcode
	 */
	public function add_shortcode(ShortcodeInterface $shortcode)
	{
		$this->add($shortcode->id(), $shortcode);
		add_shortcode($shortcode->tag(), [$this, $shortcode->id()]);
	}

	/**
	 * Remove a shortcode.
	 *
	 * @param string $tag
	 */
	public function remove_shortcode(string $tag)
	{
		$shortcode = array_reduce($this->shortcodes, function($carry, $item) use ($tag) {
			if (null !== $carry) return $carry;

			return $item->tag() === $tag ? $item : null;
		}, null);

		if (null !== $shortcode) {
			$this->remove($shortcode->id());
		}
	}

	public function all(): array
	{
		return $this->shortcodes;
	}

	public function get(string $name): ?ShortcodeInterface
	{
		return $this->shortcodes[$name] ?? null;
	}

	public function remove($name)
	{
		foreach ((array) $name as $n) {
			$shortcode = $this->shortcodes[$n];

			remove_shortcode($shortcode->tag());
			unset($this->shortcodes[$n]);
		}
	}

	public function __call(string $name, array $arguments)
	{
		return $this->invoker->call($this->get($name), $arguments);
	}
}