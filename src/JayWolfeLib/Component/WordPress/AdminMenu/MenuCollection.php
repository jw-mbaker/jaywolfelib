<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\AdminMenu;

use JayWolfeLib\Collection\AbstractInvokerCollection;
use Symfony\Component\HttpFoundation\Response;
use Invoker\InvokerInterface;

class MenuCollection extends AbstractInvokerCollection
{
	/**
	 * @var array<string, MenuPageInterface>
	 */
	private array $menu_pages = [];

	private function add(MenuPageInterface $menu_page)
	{
		$this->menu_pages[(string) $menu_page->id()] = $menu_page;
	}

	public function all(): array
	{
		return $this->menu_pages;
	}

	public function get(string $slug): ?MenuPageInterface
	{
		$menu_page = array_reduce($this->menu_pages, function($carry, $item) use ($slug) {
			if (null !== $carry) return $carry;

			if ($slug === $item->slug()) {
				return $item;
			}

			return null;
		}, null);

		return $menu_page;
	}

	/**
	 * Removes a menu page by slug.
	 *
	 * @param string $slug
	 * @return bool
	 */
	public function remove_menu_page(string $slug): bool
	{		
		$menu_page = $this->get($slug);

		if (null !== $menu_page) {
			$this->remove($menu_page);
			return true;
		}

		return false;
	}

	public function remove_submenu_page(string $slug): bool
	{
		return $this->remove_menu_page($slug);
	}

	/**
	 * Removes a menu page from the collection.
	 *
	 * @param MenuPageInterface $menu_page
	 */
	private function remove(MenuPageInterface $menu_page)
	{
		switch ($menu_page::MENU_TYPE) {
			case 'menu_page':
				remove_menu_page($menu_page->slug());
				break;
			case 'submenu_page':
				remove_submenu_page($menu_page->parent_slug(), $menu_page->slug());
				break;
		}
		
		unset($this->menu_pages[(string) $menu_page->id()]);
	}

	/**
	 * Add a menu page.
	 * 
	 * @return string
	 */
	public function menu_page(MenuPage $menu_page): string
	{
		$this->add($menu_page);
		return add_menu_page(
			$menu_page->page_title(),
			$menu_page->menu_title(),
			$menu_page->capability(),
			$menu_page->slug(),
			[$this, (string) $menu_page->id()],
			$menu_page->icon_url(),
			$menu_page->position()
		);
	}

	/**
	 * Add a sub-menu page.
	 *
	 * @param SubMenuPage $sub_menu_page
	 * @return string|false
	 */
	public function sub_menu_page(SubMenuPage $sub_menu_page)
	{
		$this->add($sub_menu_page);
		return add_submenu_page(
			$sub_menu_page->parent_slug(),
			$sub_menu_page->page_title(),
			$sub_menu_page->menu_title(),
			$sub_menu_page->capability(),
			$sub_menu_page->slug(),
			[$this, (string) $sub_menu_page->id()],
			$sub_menu_page->position()
		);
	}

	public function __call(string $name, array $arguments)
	{
		$response = $this->resolve($this->menu_pages[$name], $arguments);

		if ($response instanceof Response) {
			$response->send();
		}

		return $response;
	}
}