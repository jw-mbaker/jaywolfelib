<?php

namespace JayWolfeLib\Tests;

use JayWolfeLib\JayWolfeLib;
use JayWolfeLib\Component\Config\ConfigCollection;
use JayWolfeLib\Component\Config\ConfigInterface;
use JayWolfeLib\Component\WordPress\Filter\FilterCollection;
use JayWolfeLib\Component\WordPress\Filter\HookInterface;
use JayWolfeLib\Component\WordPress\PostType\PostTypeCollection;
use JayWolfeLib\Component\WordPress\PostType\PostTypeInterface;
use JayWolfeLib\Component\WordPress\AdminMenu\MenuCollection;
use JayWolfeLib\Component\WordPress\AdminMenu\MenuPageInterface;
use JayWolfeLib\Component\WordPress\AdminMenu\MenuPage;
use JayWolfeLib\Component\WordPress\AdminMenu\SubMenuPage;
use JayWolfeLib\Component\WordPress\Widget\WidgetCollection;
use JayWolfeLib\Component\WordPress\Widget\WidgetInterface;
use JayWolfeLib\Component\WordPress\MetaBox\MetaBoxCollection;
use JayWolfeLib\Component\WordPress\MetaBox\MetaBoxInterface;
use JayWolfeLib\Component\WordPress\Shortcode\ShortcodeCollection;
use JayWolfeLib\Component\WordPress\Shortcode\ShortcodeInterface;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use WP_Mock;
use WP_Mock\InvokedFilterValue;
use WP_Mock\Matcher\AnyInstance;
use Mockery;

use const JayWolfeLib\PRODUCTION;
use const JayWolfeLib\CACHE_DIR;

class JayWolfeLibTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private $containerBuilder;

	public function setUp(): void
	{
		$this->containerBuilder = new ContainerBuilder();
		$this->container = $this->createDevContainer();
		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
	}

	public function testEnableCompilation()
	{
		WP_Mock::onFilter('jwlib_dev')
			->with(true)
			->reply(false);

		$jwlib = new JayWolfeLib($this->containerBuilder);
		$container = $jwlib->add_definitions();

		$this->assertInstanceOf(ContainerInterface::class, $container);
		$this->assertTrue(class_exists(\JwLibCompiledContainer::class));
		$this->assertFileExists(CACHE_DIR . '/JwLibCompiledContainer.php');

		unlink(CACHE_DIR . '/JwLibCompiledContainer.php');
	}

	public function testCanAddDefinitions()
	{
		WP_Mock::onAction('jwlib_container_definitions')
			->with($this->containerBuilder)
			->perform(function() {
				$this->containerBuilder->addDefinitions([
					'test' => 123,
					'test2' => 456,
					\MockClass::class => \MockClass::class
				]);
			});

		$jwlib = new JayWolfeLib($this->containerBuilder);
		$container = $jwlib->add_definitions();

		$this->assertEquals($container->get('test'), 123);
		$this->assertEquals($container->get('test2'), 456);
		$this->assertEquals($container->get(\MockClass::class), \MockClass::class);
		$this->assertFalse($container->has('test1234'));
	}

	/**
	 * @depends testCanAddDefinitions
	 */
	public function testCanAddToConfigCollection()
	{
		$jwlib = new JayWolfeLib($this->containerBuilder);

		$collection = $this->container->get(ConfigCollection::class);

		WP_Mock::onAction('jwlib_container_definitions')
			->with($this->containerBuilder)
			->perform(function() use ($collection) {
				$this->containerBuilder->addDefinitions([
					ConfigCollection::class => $collection
				]);
			});

		WP_Mock::onAction('jwlib_config')
			->with($collection)
			->perform(function() use ($collection) {
				$collection->add('test', Mockery::mock(ConfigInterface::class));
			});

		$jwlib->init();

		$this->assertInstanceOf(ConfigInterface::class, $collection->get('test'));
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testCanCheckConfigs()
	{
		$collection = $this->container->get(ConfigCollection::class);

		$configs = array(
			'test1' => Mockery::mock(ConfigInterface::class),
			'test2' => Mockery::mock(ConfigInterface::class),
			'test3' => Mockery::mock(ConfigInterface::class)
		);

		foreach ($configs as $key => $config) {
			$collection->add($key, $config);
		}

		$configs['test1']->expects()->requirements_met()->andReturn(true);
		$configs['test2']->expects()->requirements_met()->andReturn(false);
		$configs['test3']->expects()->requirements_met()->andReturn(true);

		$configs['test2']->expects()->get_errors()->andReturn([
			(object) [
				'error_message' => 'test',
				'info' => 'test'
			]
		]);

		$configs['test2']->expects()->get('plugin_file')->andReturn('mock/plugin.php');

		WP_Mock::userFunction('deactivate_plugins', ['times' => 1]);
		WP_Mock::userFunction('plugin_basename', ['times' => 1]);
		WP_Mock::userFunction('wp_die', ['times' => 1]);
		WP_Mock::userFunction('wp_kses_post', ['times' => 1]);

		$jwlib = new JayWolfeLib($this->containerBuilder);
		$jwlib->check_config($collection);
	}

	/**
	 * @depends testCanAddDefinitions
	 */
	public function testCanAddtoFilterCollection()
	{
		$collection = $this->container->get(FilterCollection::class);
		$jwlib = new JayWolfeLib($this->containerBuilder);

		WP_Mock::onAction('jwlib_container_definitions')
		->with($this->containerBuilder)
		->perform(function() use ($collection) {
			$this->containerBuilder->addDefinitions([
				FilterCollection::class => $collection
			]);
		});

		$hook = Mockery::mock(HookInterface::class);
		$hook->expects()->hook()->andReturn('test');
		$hook->expects()->id()->twice()->andReturn(spl_object_hash($hook));
		$hook->expects()->get('priority')->andReturn(10);
		$hook->expects()->get('num_args')->andReturn(1);

		WP_Mock::onAction('jwlib_hooks')
			->with($collection)
			->perform(function() use ($collection, $hook) {
				$collection->add_filter($hook);
			});

		$jwlib->init();

		$this->assertInstanceOf(HookInterface::class, $collection->get(spl_object_hash($hook)));
	}

	/**
	 * @depends testCanAddDefinitions
	 */
	public function testCanAddPostTypeToCollection()
	{
		$collection = $this->container->get(PostTypeCollection::class);
		$jwlib = new JayWolfeLib($this->containerBuilder);

		WP_Mock::onAction('jwlib_container_definitions')
			->with($this->containerBuilder)
			->perform(function() use ($collection) {
				$this->containerBuilder->addDefinitions([
					PostTypeCollection::class => $collection
				]);
			});

		WP_Mock::userFunction('register_post_type', ['times' => 1]);
		WP_Mock::userFunction('is_wp_error', ['times' => 1, 'return' => false]);

		$post_type = Mockery::mock(PostTypeInterface::class);
		$post_type->expects()->id()->andReturn(spl_object_hash($post_type));
		$post_type->expects()->post_type()->andReturn('test');
		$post_type->expects()->args()->andReturn([]);

		WP_Mock::onAction('jwlib_post_types')
			->with($collection)
			->perform(function() use ($collection, $post_type) {
				$collection->register_post_type($post_type);
			});

		$jwlib->init();

		$this->assertInstanceOf(PostTypeInterface::class, $collection->get(spl_object_hash($post_type)));
	}

	/**
	 * @depends testCanAddDefinitions
	 */
	public function testCanAddMenuPageToCollection()
	{
		$collection = $this->container->get(MenuCollection::class);
		$jwlib = new JayWolfeLib($this->containerBuilder);

		WP_Mock::onAction('jwlib_container_definitions')
			->with($this->containerBuilder)
			->perform(function() use ($collection) {
				$this->containerBuilder->addDefinitions([
					MenuCollection::class => $collection
				]);
			});

		WP_Mock::expectActionAdded('admin_menu', '__CLOSURE__');
		WP_Mock::userFunction('add_menu_page', ['times' => 1, 'return' => '']);

		$mp = Mockery::mock(MenuPage::class);
		$mp->expects()->id()->twice()->andReturn(spl_object_hash($mp));
		$mp->expects()->get('page_title')->andReturn('test');
		$mp->expects()->get('menu_title')->andReturn('test');
		$mp->expects()->get('capability')->andReturn('administrator');
		$mp->expects()->slug()->andReturn('test');
		$mp->expects()->get('icon_url')->andReturn('');
		$mp->expects()->get('position')->andReturn(null);

		WP_Mock::onAction('jwlib_admin_menu')
			->with($collection)
			->perform(fn() => $collection->menu_page($mp));

		$jwlib->init();

		do_action('jwlib_admin_menu', $jwlib->get_container()->get(MenuCollection::class));

		$this->assertInstanceOf(MenuPage::class, $collection->get(spl_object_hash($mp)));
	}

	/**
	 * @depends testCanAddDefinitions
	 */
	public function testCanAddSubMenuPageToCollection()
	{
		$collection = $this->container->get(MenuCollection::class);
		$jwlib = new JayWolfeLib($this->containerBuilder);

		WP_Mock::onAction('jwlib_container_definitions')
			->with($this->containerBuilder)
			->perform(function() use ($collection) {
				$this->containerBuilder->addDefinitions([
					MenuCollection::class => $collection
				]);
			});

		WP_Mock::expectActionAdded('admin_menu', '__CLOSURE__');
		WP_Mock::userFunction('add_submenu_page', ['times' => 1, 'return' => '']);

		$smp = Mockery::mock(SubMenuPage::class);
		$smp->expects()->id()->twice()->andReturn(spl_object_hash($smp));
		$smp->expects()->parent_slug()->andReturn('parent-test');
		$smp->expects()->get('page_title')->andReturn('test');
		$smp->expects()->get('menu_title')->andReturn('test');
		$smp->expects()->get('capability')->andReturn('administrator');
		$smp->expects()->slug()->andReturn('test');
		$smp->expects()->get('position')->andReturn(null);

		WP_Mock::onAction('jwlib_admin_menu')
			->with($collection)
			->perform(fn() => $collection->sub_menu_page($smp));

		$jwlib->init();

		do_action('jwlib_admin_menu', $jwlib->get_container()->get(MenuCollection::class));

		$this->assertInstanceOf(SubMenuPage::class, $collection->get(spl_object_hash($smp)));
	}

	/**
	 * @depends testCanAddDefinitions
	 */
	public function testCanAddWidgetToCollection()
	{
		$collection = $this->container->get(WidgetCollection::class);
		$jwlib = new JayWolfeLib($this->containerBuilder);

		WP_Mock::onAction('jwlib_container_definitions')
			->with($this->containerBuilder)
			->perform(function() use ($collection) {
				$this->containerBuilder->addDefinitions([
					WidgetCollection::class => $collection
				]);
			});

		WP_Mock::expectActionAdded('widgets_init', '__CLOSURE__');
		WP_Mock::userFunction('register_widget', ['times' => 1, 'args' => [\WP_Widget::class]]);

		$widget = Mockery::mock(WidgetInterface::class);
		$widget->expects()->id()->andReturn(spl_object_hash($widget));
		$widget->expects()->widget()->andReturn(\WP_Widget::class);

		WP_Mock::onAction('jwlib_register_widgets')
			->with($collection)
			->perform(fn() => $collection->register_widget($widget));

		$jwlib->init();

		do_action('jwlib_register_widgets', $jwlib->get_container()->get(WidgetCollection::class));

		$this->assertInstanceOf(WidgetInterface::class, $collection->get(spl_object_hash($widget)));
	}

	/**
	 * @depends testCanAddDefinitions
	 */
	public function testCanAddMetaBoxToCollection()
	{
		$collection = $this->container->get(MetaBoxCollection::class);
		$jwlib = new JayWolfeLib($this->containerBuilder);

		WP_Mock::onAction('jwlib_container_definitions')
			->with($this->containerBuilder)
			->perform(function() use ($collection) {
				$this->containerBuilder->addDefinitions([
					MetaBoxCollection::class => $collection
				]);
			});

		WP_Mock::expectActionAdded('add_meta_boxes', '__CLOSURE__');
		WP_Mock::userFunction('add_meta_box', ['times' => 1]);

		$mb = Mockery::mock(MetaBoxInterface::class);
		$mb->expects()->id()->twice()->andReturn(spl_object_hash($mb));
		$mb->expects()->meta_id()->andReturn('test');
		$mb->expects()->title()->andReturn('test');
		$mb->expects()->get('screen')->andReturn(null);
		$mb->expects()->get('context')->andReturn('advanced');
		$mb->expects()->get('priority')->andReturn('default');
		$mb->expects()->get('callback_args')->andReturn([]);

		WP_Mock::onAction('jwlib_meta_boxes')
			->with($collection)
			->perform(fn() => $collection->add_meta_box($mb));

		$jwlib->init();

		do_action('jwlib_meta_boxes', $jwlib->get_container()->get(MetaBoxCollection::class));

		$this->assertInstanceOf(MetaBoxInterface::class, $collection->get(spl_object_hash($mb)));
	}

	/**
	 * @depends testCanAddDefinitions
	 */
	public function testCanAddShortcodeToCollection()
	{
		$collection = $this->container->get(ShortcodeCollection::class);
		$jwlib = new JayWolfeLib($this->containerBuilder);

		WP_Mock::onAction('jwlib_container_definitions')
			->with($this->containerBuilder)
			->perform(function() use ($collection) {
				$this->containerBuilder->addDefinitions([
					ShortcodeCollection::class => $collection
				]);
			});

		WP_Mock::userFunction('add_shortcode', ['times' => 1]);

		$shortcode = Mockery::mock(ShortcodeInterface::class);
		$shortcode->expects()->id()->twice()->andReturn(spl_object_hash($shortcode));
		$shortcode->expects()->tag()->andReturn('test');

		WP_Mock::onAction('jwlib_shortcodes')
			->with($collection)
			->perform(fn() => $collection->add_shortcode($shortcode));

		$jwlib->init();

		$this->assertInstanceOf(ShortcodeInterface::class, $collection->get(spl_object_hash($shortcode)));
	}

	public function testShouldThrowExceptionOnInit()
	{
		$jwlib = new JayWolfeLib($this->containerBuilder);

		$e = new \Exception('test');

		WP_Mock::onAction('jwlib_container_definitions')
			->with($this->containerBuilder)
			->perform(function() use ($e) {
				throw $e;
			});

		$this->expectException(\Exception::class);

		$jwlib->init();
	}

	public function testLoad()
	{
		WP_Mock::expectActionAdded('jwlib_container_definitions', '__CLOSURE__', -1, 1);
		WP_Mock::expectActionAdded('init', [new AnyInstance(JayWolfeLib::class), 'init'], 0, 1);

		WP_Mock::userFunction('did_action', [
			'args' => 'init',
			'return' => false,
			'times' => 1
		]);

		$bool = JayWolfeLib::load(null, $this->containerBuilder);

		$this->assertTrue($bool);
	}

	public function testShouldAddJwLibConfigAction()
	{
		WP_Mock::expectActionAdded('jwlib_config', '__CLOSURE__');

		WP_Mock::userFunction('did_action', [
			'args' => 'init',
			'return' => false,
			'times' => 1
		]);

		$bool = JayWolfeLib::load(MOCK_CONFIG_FILE, $this->containerBuilder);
		
		$this->assertTrue($bool);
	}

	public function testShouldThrowExceptionIfDidInitAction()
	{
		WP_Mock::userFunction('did_action', [
			'args' => 'init',
			'return' => true,
			'times' => 1
		]);

		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage(
			sprintf('%s::load must be called before "init"', JayWolfeLib::class)
		);

		JayWolfeLib::load(null, $this->containerBuilder);
	}
}