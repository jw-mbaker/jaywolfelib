<?php

namespace JayWolfeLib\Tests\Component\WordPress\AdminMenu;

use JayWolfeLib\Component\WordPress\AdminMenu\MenuCollection;
use JayWolfeLib\Component\WordPress\AdminMenu\MenuPage;
use JayWolfeLib\Component\WordPress\AdminMenu\SubMenuPage;
use JayWolfeLib\Tests\Component\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WP_Mock;
use Mockery;

class MenuCollectionTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private $request;

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
		$mp = Mockery::mock(MenuPage::class);
		$mp->expects()->get('page_title')->andReturn('test');
		$mp->expects()->get('menu_title')->andReturn('test');
		$mp->expects()->get('capability')->andReturn('administrator');
		$mp->expects()->slug()->andReturn('test');
		$mp->expects()->id()->twice()->andReturn(spl_object_hash($mp));
		$mp->expects()->get('icon_url')->andReturn('');
		$mp->expects()->get('position')->andReturn(null);

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
		$smp = Mockery::mock(SubMenuPage::class);
		$smp->expects()->parent_slug()->andReturn('parent-test');
		$smp->expects()->get('page_title')->andReturn('test');
		$smp->expects()->get('menu_title')->andReturn('test');
		$smp->expects()->get('capability')->andReturn('administrator');
		$smp->expects()->slug()->andReturn('test');
		$smp->expects()->id()->twice()->andReturn(spl_object_hash($smp));
		$smp->expects()->get('position')->andReturn(null);

		$this->collection->sub_menu_page($smp);
		$this->assertContains($smp, $this->collection->all());
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanRemoveMenuPage()
	{
		$mp = Mockery::mock(MenuPage::class);
		$mp->expects()->get('page_title')->andReturn('test');
		$mp->expects()->get('menu_title')->andReturn('test');
		$mp->expects()->get('capability')->andReturn('administrator');
		$mp->expects()->slug()->times(3)->andReturn('test');
		$mp->expects()->id()->times(3)->andReturn(spl_object_hash($mp));
		$mp->expects()->get('icon_url')->andReturn('');
		$mp->expects()->get('position')->andReturn(null);

		$this->collection->menu_page($mp);
		$this->assertContains($mp, $this->collection->all());

		$this->collection->remove_menu_page('test');
		$this->assertNotContains($mp, $this->collection->all());
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanRemoveSubMenuPage()
	{
		$smp = Mockery::mock(SubMenuPage::class);
		$smp->expects()->parent_slug()->twice()->andReturn('parent-test');
		$smp->expects()->get('page_title')->andReturn('test');
		$smp->expects()->get('menu_title')->andReturn('test');
		$smp->expects()->get('capability')->andReturn('administrator');
		$smp->expects()->slug()->times(3)->andReturn('test');
		$smp->expects()->id()->times(3)->andReturn(spl_object_hash($smp));
		$smp->expects()->get('position')->andReturn(null);

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
	public function testCanGetMenuPage()
	{
		$mp = Mockery::mock(MenuPage::class);
		$mp->expects()->get('page_title')->andReturn('test');
		$mp->expects()->get('menu_title')->andReturn('test');
		$mp->expects()->get('capability')->andReturn('administrator');
		$mp->expects()->slug()->andReturn('test');
		$mp->expects()->id()->twice()->andReturn(spl_object_hash($mp));
		$mp->expects()->get('icon_url')->andReturn('');
		$mp->expects()->get('position')->andReturn(null);

		$this->collection->menu_page($mp);
		$this->assertSame($mp, $this->collection->get(spl_object_hash($mp)));
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanGetSubMenuPage()
	{
		$smp = Mockery::mock(SubMenuPage::class);
		$smp->expects()->parent_slug()->andReturn('parent-test');
		$smp->expects()->get('page_title')->andReturn('test');
		$smp->expects()->get('menu_title')->andReturn('test');
		$smp->expects()->get('capability')->andReturn('administrator');
		$smp->expects()->slug()->andReturn('test');
		$smp->expects()->id()->twice()->andReturn(spl_object_hash($smp));
		$smp->expects()->get('position')->andReturn(null);

		$this->collection->sub_menu_page($smp);
		$this->assertSame($smp, $this->collection->get(spl_object_hash($smp)));
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeMenuPage()
	{
		$mp = new MenuPage(
			'test',
			function() {
				$this->assertTrue(true);
			},
			[
				'page_title' => 'test',
				'menu_title' => 'test',
				'capability' => 'administrator'
			]
		);

		$this->collection->menu_page($mp);

		$this->assertSame($mp, $this->collection->get($mp->id()));

		call_user_func([$this->collection, $mp->id()]);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeSubMenuPage()
	{
		$smp = new SubMenuPage(
			'test',
			'parent-test',
			function() {
				$this->assertTrue(true);
			},
			[
				'page_title' => 'test',
				'menu_title' => 'test',
				'capability' => 'administrator'
			]
		);

		$this->collection->sub_menu_page($smp);

		$this->assertSame($smp, $this->collection->get($smp->id()));

		call_user_func([$this->collection, $smp->id()]);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeMenuPageWithTypeHint()
	{
		$mp = new MenuPage(
			'test',
			function(Request $request, MockTypeHint $th) {
				$this->assertInstanceOf(Request::class, $request);
				$this->assertInstanceOf(MockTypeHint::class, $th);
			},
			[
				'page_title' => 'test',
				'menu_title' => 'test',
				'capability' => 'administrator'
			]
		);

		$this->collection->menu_page($mp);

		$this->assertSame($mp, $this->collection->get($mp->id()));

		call_user_func([$this->collection, $mp->id()]);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testCanInvokeSubMenuPageWithTypeHint()
	{
		$smp = new SubMenuPage(
			'test',
			'parent-test',
			function(Request $request, MockTypeHint $th) {
				$this->assertInstanceOf(Request::class, $request);
				$this->assertInstanceOf(MockTypeHint::class, $th);
			},
			[
				'page_title' => 'test',
				'menu_title' => 'test',
				'capability' => 'administrator'
			]
		);

		$this->collection->sub_menu_page($smp);

		$this->assertSame($smp, $this->collection->get($smp->id()));

		call_user_func([$this->collection, $smp->id()]);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testMenuPageCanTakeRequestObjectAndHandlResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$mp = new MenuPage(
			'test',
			function(Request $request) use ($response) {
				$this->assertInstanceOf(Request::class, $request);
				return $response;
			},
			[
				'page_title' => 'test',
				'menu_title' => 'test',
				'capability' => 'administrator'
			]
		);

		$this->collection->menu_page($mp);

		$this->assertSame($mp, $this->collection->get($mp->id()));

		$response->expects()->send();

		call_user_func([$this->collection, $mp->id()]);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 * @group collection
	 */
	public function testSubMenuPageCanTakeRequestObjectAndHandleResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$smp = new SubMenuPage(
			'test',
			'parent-test',
			function(Request $request) use ($response) {
				$this->assertInstanceOf(Request::class, $request);
				return $response;
			},
			[
				'page_title' => 'test',
				'menu_title' => 'test',
				'capability' => 'administrator'
			]
		);

		$this->collection->sub_menu_page($smp);

		$this->assertSame($smp, $this->collection->get($smp->id()));

		$response->expects()->send();

		call_user_func([$this->collection, $smp->id()]);
	}
}