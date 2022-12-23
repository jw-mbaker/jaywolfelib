<?php

namespace JayWolfeLib\Component\WordPress\AdminMenu;

use JayWolfeLib\Collection\AbstractInvokerCollection;
use Symfony\Component\HttpFoundation\Response;
use Invoker\InvokerInterface;

class MenuCollection extends AbstractInvokerCollection
{
	/**
	 * @var array<string, MenuPageInterface>
	 */
	private $menu_pages = [];

	public function add(string $name, MenuPageInterface $menu_page)
	{
		$this->menu_pages[$name] = $menu_page;
	}

	public function all(): array
	{
		return $this->menu_pages;
	}

	public function get(string $name): ?MenuPageInterface
	{
		return $this->menu_pages[$name] ?? null;
	}

	/**
	 * Removes a menu page or an array of menu pages by name from the collection.
	 *
	 * @param string|string[] $name
	 */
	public function remove($name)
	{
		foreach ((array) $name as $n) {
			$menu_page = $this->menu_pages[$n];
			$class = get_class($menu_page);

			switch ($class::MENU_TYPE) {
				case 'menu_page':
					remove_menu_page($menu_page->slug());
					break;
				case 'submenu_page':
					remove_submenu_page($menu_page->parent_slug(), $menu_page->slug());
					break;
			}
			
			unset($this->menu_pages[$n]);
		}
	}

	/**
	 * Add a menu page.
	 * 
	 * @return string
	 */
	public function menu_page(MenuPage $menu_page): string
	{
		$this->add($menu_page->id(), $menu_page);
		return add_menu_page(
			$menu_page->get('page_title'),
			$menu_page->get('menu_title'),
			$menu_page->get('capability'),
			$menu_page->slug(),
			[$this, $menu_page->id()],
			$menu_page->get('icon_url'),
			$menu_page->get('position')
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
		$this->add($sub_menu_page->id(), $sub_menu_page);
		return add_submenu_page(
			$sub_menu_page->parent_slug(),
			$sub_menu_page->get('page_title'),
			$sub_menu_page->get('menu_title'),
			$sub_menu_page->get('capability'),
			$sub_menu_page->slug(),
			[$this, $sub_menu_page->id()],
			$sub_menu_page->get('position')
		);
	}

	public function __call(string $name, array $arguments)
	{
		$this->invoker->call($this->get($name), $arguments);
	}
}