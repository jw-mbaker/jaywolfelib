<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\WordPress\AdminMenu;

use JayWolfeLib\WordPress\AdminMenu\SubMenuPage;
use JayWolfeLib\WordPress\AdminMenu\MenuId;
use JayWolfeLib\Tests\Invoker\MockTypeHint;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WP_Mock;
use Mockery;

class SubMenuPageTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	public function setUp(): void
	{
		$this->container = $this->createDevContainer();
		$this->container->set(Request::class, Mockery::mock(Request::class));
		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 */
	public function testCanInvokeSubMenuPage()
	{
		$smp = SubMenuPage::create([
			SubMenuPage::SLUG => 'test',
			SubMenuPage::PARENT_SLUG => 'parent-test',
			SubMenuPage::CALLABLE => function() {
				$this->assertTrue(true);
			}
		]);

		$this->container->call($smp);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 */
	public function testCanInvokeSubMenuPageWithTypeHint()
	{
		$smp = SubMenuPage::create([
			SubMenuPage::SLUG => 'test',
			SubMenuPage::PARENT_SLUG => 'parent-test',
			SubMenuPage::CALLABLE => function(Request $request, MockTypeHint $th) {
				$this->assertInstanceOf(Request::class, $request);
				$this->assertInstanceOf(MockTypeHint::class, $th);
			}
		]);

		$this->container->call($smp);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 */
	public function testSubMenuPageCanHandleResponseObject()
	{
		$response = Mockery::mock(Response::class);

		$smp = SubMenuPage::create([
			SubMenuPage::SLUG => 'test',
			SubMenuPage::PARENT_SLUG => 'parent-test',
			SubMenuPage::CALLABLE => function() use ($response) {
				$this->assertTrue(true);
				return $response;
			}
		]);

		$this->container->call($smp);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
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
			}
		]);

		$this->container->call($smp);
	}

	/**
	 * @group admin_menu
	 * @group wordpress
	 */
	public function testSubMenuPageIdMethodShouldReturnMenuId()
	{
		$smp = SubMenuPage::create([
			SubMenuPage::SLUG => 'test',
			SubMenuPage::PARENT_SLUG => 'parent-test',
			SubMenuPage::CALLABLE => function() {}
		]);

		$id = $smp->id();
		$this->assertInstanceOf(MenuId::class, $id);
		$this->assertSame((string) $id, spl_object_hash($smp));
	}
}