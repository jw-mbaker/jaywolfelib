<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\AdminMenu;

use JayWolfeLib\Invoker\AbstractInvokerCollection;
use Symfony\Component\HttpFoundation\Response;
use Invoker\InvokerInterface;

class MenuCollection extends AbstractInvokerCollection
{
	/**
	 * @var array<string, MenuPageInterface>
	 */
	private array $menuPages = [];

	private function add(MenuPageInterface $menuPage)
	{
		$this->menuPages[(string) $menuPage->id()] = $menuPage;
	}

	/**
	 * @return array<string, MenuPageInterface>
	 */
	public function all(): array
	{
		return $this->menuPages;
	}

	public function getById(MenuId $id): ?MenuPageInterface
	{
		return $this->menuPages[(string) $id] ?? null;
	}

	public function get(string $slug): ?MenuPageInterface
	{
		$menuPage = array_reduce($this->menuPages, function($carry, $item) use ($slug) {
			if (null !== $carry) return $carry;

			if ($slug === $item->slug()) {
				return $item;
			}

			return null;
		}, null);

		return $menuPage;
	}

	/**
	 * Removes a menu page by slug.
	 *
	 * @param string $slug
	 * @return bool
	 */
	public function removeMenuPage(string $slug): bool
	{		
		$menuPage = $this->get($slug);

		if (null !== $menuPage) {
			$this->remove($menuPage);
			return true;
		}

		return false;
	}

	public function removeSubMenuPage(string $slug): bool
	{
		return $this->removeMenuPage($slug);
	}

	/**
	 * Removes a menu page from the collection.
	 *
	 * @param MenuPageInterface $menu_page
	 */
	private function remove(MenuPageInterface $menuPage)
	{
		switch (get_class($menuPage)) {
			case MenuPage::class:
				remove_menu_page($menuPage->slug());
				break;
			case SubMenuPage::class:
				remove_submenu_page($menuPage->parentSlug(), $menuPage->slug());
				break;
		}
		
		unset($this->menuPages[(string) $menuPage->id()]);
	}

	/**
	 * Add a menu page.
	 * 
	 * @return string
	 */
	public function menuPage(MenuPage $menuPage): string
	{
		$this->add($menuPage);
		return add_menu_page(
			$menuPage->pageTitle(),
			$menuPage->menuTitle(),
			$menuPage->capability(),
			$menuPage->slug(),
			[$this, (string) $menuPage->id()],
			$menuPage->iconUrl(),
			$menuPage->position()
		);
	}

	/**
	 * Add a sub-menu page.
	 *
	 * @param SubMenuPage $subMenuPage
	 * @return string|false
	 */
	public function subMenuPage(SubMenuPage $subMenuPage)
	{
		$this->add($subMenuPage);
		return add_submenu_page(
			$subMenuPage->parentSlug(),
			$subMenuPage->pageTitle(),
			$subMenuPage->menuTitle(),
			$subMenuPage->capability(),
			$subMenuPage->slug(),
			[$this, (string) $subMenuPage->id()],
			$subMenuPage->position()
		);
	}

	public function __call(string $name, array $arguments)
	{
		$response = $this->resolve($this->menuPages[$name], $arguments);

		if ($response instanceof Response) {
			$response->send();
		}

		return $response;
	}
}