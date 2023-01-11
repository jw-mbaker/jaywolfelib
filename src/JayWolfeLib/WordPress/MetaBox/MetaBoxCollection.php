<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\MetaBox;

use JayWolfeLib\Invoker\AbstractInvokerCollection;
use Symfony\Component\HttpFoundation\Response;
use WP_Screen;

class MetaBoxCollection extends AbstractInvokerCollection
{
	/**
	 * @var array<string, MetaBoxInterface>
	 */
	private array $metaBoxes = [];

	private function add(MetaBoxInterface $metaBox)
	{
		$this->metaBoxes[(string) $metaBox->id()] = $metaBox;
	}

	public function addMetaBox(MetaBoxInterface $metaBox)
	{
		$this->add($metaBox);
		add_meta_box(
			$metaBox->metaId(),
			$metaBox->title(),
			[$this, (string) $metaBox->id()],
			$metaBox->screen(),
			$metaBox->context(),
			$metaBox->priority(),
			$metaBox->callbackArgs()
		);
	}

	/**
	 * Remove a meta box.
	 *
	 * @param string $metaId
	 * @param string|array|WP_Screen $screen
	 * @param string $context
	 * @return bool
	 */
	public function removeMetaBox(string $metaId, $screen, string $context): bool
	{
		$metaBox = $this->get($metaId, $screen, $context);

		if (null !== $metaBox) {
			$this->remove($metaBox);
			return true;
		}

		return false;
	}

	public function all(): array
	{
		return $this->metaBoxes;
	}

	public function getById(MetaBoxId $id): ?MetaBoxInterface
	{
		return $this->metaBoxes[(string) $id] ?? null;
	}

	/**
	 * Get the meta box object filtered by its id, screen, and context.
	 *
	 * @param string $metaId
	 * @param string|array|WP_Screen $screen
	 * @param string $context
	 * @return MetaBoxInterface|null
	 */
	public function get(string $metaId, $screen, string $context): ?MetaBoxInterface
	{
		$metaBox = array_reduce($this->metaBoxes, function($carry, $item) use ($metaId, $screen, $context) {
			if (null !== $carry) return $carry;

			if (
				$item->metaId() === $metaId &&
				$item->screen() === $screen &&
				$item->context() === $context
			) {
				return $item;
			}

			return null;
		}, null);

		return $metaBox;
	}

	private function remove(MetaBoxInterface $metaBox)
	{
		remove_meta_box($metaBox->metaId(), $metaBox->screen(), $metaBox->context());
		unset($this->metaBoxes[(string) $metaBox->id()]);
	}

	public function __call(string $name, array $arguments)
	{
		$response = $this->resolve($this->metaBoxes[$name], $arguments);

		if ($response instanceof Response) {
			$response->send();
		}

		return $response;
	}
}