<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\Shortcode;

use JayWolfeLib\Collection\AbstractInvokerCollection;

class ShortcodeCollection extends AbstractInvokerCollection
{
	/**
	 * @var array<string, ShortcodeInterface>
	 */
	private array $shortcodes = [];

	private function add(ShortcodeInterface $shortcode)
	{
		$this->shortcodes[(string) $shortcode->id()] = $shortcode;
	}

	/**
	 * Add a shortcode.
	 *
	 * @param ShortcodeInterface $shortcode
	 */
	public function add_shortcode(ShortcodeInterface $shortcode)
	{
		$this->add($shortcode);
		add_shortcode($shortcode->tag(), [$this, (string) $shortcode->id()]);
	}

	/**
	 * Remove a shortcode.
	 *
	 * @param string $tag
	 * @return bool
	 */
	public function remove_shortcode(string $tag): bool
	{
		$shortcode = $this->get($tag);

		if (null !== $shortcode) {
			$this->remove($shortcode);
			return true;
		}

		return false;
	}

	public function all(): array
	{
		return $this->shortcodes;
	}

	public function get_by_id(ShortcodeId $id): ?ShortcodeInterface
	{
		return $this->shortcodes[(string) $id] ?? null;
	}

	public function get(string $tag): ?ShortcodeInterface
	{
		$shortcode = array_reduce($this->shortcodes, function($carry, $item) use ($tag) {
			if (null !== $carry) return $carry;

			return $item->tag() === $tag ? $item : null;
		}, null);

		return $shortcode;
	}

	private function remove(ShortcodeInterface $shortcode)
	{
		remove_shortcode($shortcode->tag());
		unset($this->shortcodes[(string) $shortcode->id()]);
	}

	public function __call(string $name, array $arguments)
	{
		return $this->resolve($this->shortcodes[$name], $arguments);
	}
}