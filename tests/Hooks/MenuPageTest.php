<?php

namespace JayWolfeLib\Tests\Hooks;

use JayWolfeLib\Hooks\MenuPage;
use JayWolfeLib\Hooks\Handler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\InputBag;
use WP_Mock;
use Mockery;

use function JayWolfeLib\container;

class MenuPageTest extends WP_Mock\Tools\TestCase
{
	public function setUp(): void
	{
		WP_Mock::setUp();
		WP_Mock::userFunction('add_menu_page', ['return' => '']);
		WP_Mock::userFunction('add_submenu_page', ['return' => '']);
		container(false)->set('request', fn() => Request::createFromGlobals());
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		container()->flush();
		Mockery::close();
	}

	public function testAddMenuPageReturnsHandler(): void
	{
		$handler = MenuPage::add_menu_page(
			'test',
			'test',
			'test',
			'test',
			function() {
				echo 'test';
			}
		);

		$this->assertInstanceOf(Handler::class, $handler);
	}

	public function testAddMenuPageCanInvokeCallback(): void
	{
		$_GET['test'] = 1;

		$request = container(false)->get('request');

		$callback = function (Request $request, $foo, $bar) {
			$this->assertEquals($request->query->get('test'), 1);
			$this->assertEquals($foo, 'foo');
			$this->assertEquals($bar, 'bar');
		};

		$handler = MenuPage::add_menu_page(
			'test',
			'test',
			'test',
			'test',
			$callback
		)
			->with('foo')
			->with('bar');

		call_user_func($handler);
	}
}