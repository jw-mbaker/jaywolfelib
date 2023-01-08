<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Component\WordPress\AdminMenu;

use JayWolfeLib\Component\WordPress\AdminMenu\MenuCollection;
use JayWolfeLib\Component\WordPress\AdminMenu\MenuPageInterface;
use JayWolfeLib\Component\WordPress\AdminMenu\MenuPage;
use JayWolfeLib\Component\WordPress\AdminMenu\SubMenuPage;
use JayWolfeLib\Component\WordPress\AdminMenu\MenuId;
use JayWolfeLib\Tests\Invoker\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WP_Mock;
use Mockery;

class MenuCollectionTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private MenuCollection $collection;
	private Request $request;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
		$this->collection = new MenuCollection($this->container);
		$this->request = Mockery::mock(Request::class);
		WP_Mock::setUp();
		WP_Mock::userFunction('add_menu_page', ['return' => '']);
		WP_Mock::userFunction('add_submenu_page', ['return' => '']);
		WP_Mock::userFunction('remove_menu_page');
		WP_Mock::userFunction('remove_submenu_page');
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanAddMenuPage()
	{
		$mp = $this->createMenuPage();

		$this->collection->menu_page($mp);
		$this->assertContains($mp, $this->collection->all());
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanAddSubMenuPage()
	{
		$smp = $this->createSubMenuPage();

		$this->collection->sub_menu_page($smp);
		$this->assertContains($smp, $this->collection->all());
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 * @depends testCanGetBySlug
	 */
	public function testCanRemoveMenuPage()
	{
		$mp = $this->createMenuPage();

		$this->collection->menu_page($mp);
		$this->assertContains($mp, $this->collection->all());

		$this->collection->remove_menu_page('test');
		$this->assertNotContains($mp, $this->collection->all());
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 * @depends testCanGetSubMenuPageBySlug
	 */
	public function testCanRemoveSubMenuPage()
	{
		$smp = $this->createSubMenuPage();

		$this->collection->sub_menu_page($smp);
		$this->assertContains($smp, $this->collection->all());

		$this->collection->remove_submenu_page('test');
		$this->assertNotContains($smp, $this->collection->all());
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testRemoveMenuPageReturnsFalseOnInvalidSlug()
	{
		$bool = $this->collection->remove_menu_page('test');
		$this->assertFalse($bool);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanGetBySlug()
	{
		$mp = $this->createMenuPage();

		$this->collection->menu_page($mp);
		$this->assertContains($mp, $this->collection->all());

		$obj = $this->collection->get('test');
		$this->assertInstanceOf(MenuPageInterface::class, $obj);
		$this->assertSame($obj, $mp);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanGetSubMenuPageBySlug()
	{
		$smp = $this->createSubMenuPage();

		$this->collection->sub_menu_page($smp);
		$this->assertContains($smp, $this->collection->all());

		$obj = $this->collection->get('test');
		$this->assertInstanceOf(SubMenuPage::class, $obj);
		$this->assertSame($obj, $smp);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testGetBySlugReturnsNullIfNotFound()
	{
		$obj = $this->collection->get('test');
		$this->assertNull($obj);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeMenuPage()
	{
		$mp = MenuPage::create([
			MenuPage::SLUG => 'test',
			MenuPage::CALLABLE => function() {
				$this->assertTrue(true);
			},
			MenuPage::PAGE_TITLE => 'test',
			MenuPage::MENU_TITLE => 'test',
			MenuPage::CAPABILITY => 'administrator'
		]);

		$this->collection->menu_page($mp);

		$this->assertSame($mp, $this->collection->get('test'));

		call_user_func([$this->collection, (string) $mp->id()]);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeSubMenuPage()
	{
		$smp = SubMenuPage::create([
			SubMenuPage::SLUG => 'test',
			SubMenuPage::PARENT_SLUG => 'parent-test',
			SubMenuPage::CALLABLE => function() {
				$this->assertTrue(true);
			},
			SubMenuPage::PAGE_TITLE => 'test',
			SubMenuPage::MENU_TITLE => 'test',
			SubMenuPage::CAPABILITY => 'administrator'
		]);

		$this->collection->sub_menu_page($smp);

		$this->assertSame($smp, $this->collection->get($smp->slug()));

		call_user_func([$this->collection, (string) $smp->id()]);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeMenuPageWithTypeHint()
	{
		$mp = MenuPage::create([
			MenuPage::SLUG => 'test',
			MenuPage::CALLABLE => function(Request $request, MockTypeHint $th) {
				$this->assertInstanceOf(Request::class, $request);
				$this->assertInstanceOf(MockTypeHint::class, $th);
			},
			MenuPage::PAGE_TITLE => 'test',
			MenuPage::MENU_TITLE => 'test',
			MenuPage::CAPABILITY => 'administrator'
		]);

		$this->collection->menu_page($mp);

		$this->assertSame($mp, $this->collection->get($mp->slug()));

		call_user_func([$this->collection, (string) $mp->id()]);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeSubMenuPageWithTypeHint()
	{
		$smp = SubMenuPage::create([
			SubMenuPage::SLUG => 'test',
			SubMenuPage::PARENT_SLUG => 'parent_test',
			SubMenuPage::CALLABLE => function(Request $request, MockTypeHint $th) {
				$this->assertInstanceOf(Request::class, $request);
				$this->assertInstanceOf(MockTypeHint::class, $th);
			},
			SubMenuPage::PAGE_TITLE => 'test',
			SubMenuPage::MENU_TITLE => 'test',
			SubMenuPage::CAPABILITY => 'administrator'
		]);

		$this->collection->sub_menu_page($smp);

		$this->assertSame($smp, $this->collection->get($smp->slug()));

		call_user_func([$this->collection, (string) $smp->id()]);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testMenuPageCanTakeRequestObjectAndHandlResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$mp = MenuPage::create([
			MenuPage::SLUG => 'test',
			MenuPage::CALLABLE => function(Request $request)  use ($response) {
				$this->assertInstanceOf(Request::class, $request);
				return $response;
			},
			MenuPage::PAGE_TITLE => 'test',
			MenuPage::MENU_TITLE => 'test',
			MenuPage::CAPABILITY => 'administrator'
		]);

		$this->collection->menu_page($mp);

		$this->assertSame($mp, $this->collection->get($mp->slug()));

		$response->expects()->send();

		call_user_func([$this->collection, (string) $mp->id()]);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testSubMenuPageCanTakeRequestObjectAndHandleResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$smp = SubMenuPage::create([
			SubMenuPage::SLUG => 'test',
			SubMenuPage::PARENT_SLUG => 'parent-test',
			SubMenuPage::CALLABLE => function(Request $request) use ($response) {
				$this->assertInstanceOf(Request::class, $request);
				return $response;
			},
			SubMenuPage::PAGE_TITLE => 'test',
			SubMenuPage::MENU_TITLE => 'test',
			SubMenuPage::CAPABILITY => 'administrator'
		]);

		$this->collection->sub_menu_page($smp);

		$this->assertSame($smp, $this->collection->get($smp->slug()));

		$response->expects()->send();

		call_user_func([$this->collection, (string) $smp->id()]);
	}

	private function createMenuPage(): MenuPage
	{
		return MenuPage::create([
			MenuPage::SLUG => 'test',
			MenuPage::CALLABLE => function() {}
		]);
	}

	private function createSubMenuPage(): SubMenuPage
	{
		return SubMenuPage::create([
			SubMenuPage::SLUG => 'test',
			SubMenuPage::PARENT_SLUG => 'parent-test',
			SubMenuPage::CALLABLE => function() {}
		]);
	}
}