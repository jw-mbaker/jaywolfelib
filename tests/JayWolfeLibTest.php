<?php declare(strict_types=1);

namespace JayWolfeLib\Tests;

use JayWolfeLib\JayWolfeLib;
use JayWolfeLib\Config\ConfigCollection;
use JayWolfeLib\Config\ConfigInterface;
use JayWolfeLib\WordPress\Filter\FilterCollection;
use JayWolfeLib\WordPress\Filter\HookInterface;
use JayWolfeLib\WordPress\Filter\Filter;
use JayWolfeLib\WordPress\PostType\PostTypeCollection;
use JayWolfeLib\WordPress\PostType\PostTypeInterface;
use JayWolfeLib\WordPress\PostType\PostType;
use JayWolfeLib\WordPress\AdminMenu\MenuCollection;
use JayWolfeLib\WordPress\AdminMenu\MenuPageInterface;
use JayWolfeLib\WordPress\AdminMenu\MenuPage;
use JayWolfeLib\WordPress\AdminMenu\SubMenuPage;
use JayWolfeLib\WordPress\Widget\WidgetCollection;
use JayWolfeLib\WordPress\Widget\WidgetInterface;
use JayWolfeLib\WordPress\Widget\Widget;
use JayWolfeLib\WordPress\MetaBox\MetaBoxCollection;
use JayWolfeLib\WordPress\MetaBox\MetaBoxInterface;
use JayWolfeLib\WordPress\MetaBox\MetaBox;
use JayWolfeLib\WordPress\Shortcode\ShortcodeCollection;
use JayWolfeLib\WordPress\Shortcode\ShortcodeInterface;
use JayWolfeLib\WordPress\Shortcode\Shortcode;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use WP_Mock;
use WP_Mock\InvokedFilterValue;
use WP_Mock\Matcher\AnyInstance;
use Mockery;

use const JayWolfeLib\PRODUCTION;
use const JayWolfeLib\CACHE_DIR;
use const JayWolfeLib\MOCK_CONFIG_FILE;

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
		$container = $jwlib->addDefinitions();

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
		$container = $jwlib->addDefinitions();

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

	public function testCheckAndSetConfigs()
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

		$configs['test1']->expects()->requirementsMet()->andReturn(true);
		$configs['test1']->expects()->get('plugin_file')->andReturn('test1.php');
		$configs['test2']->expects()->requirementsMet()->andReturn(false);
		$configs['test3']->expects()->requirementsMet()->andReturn(true);
		$configs['test3']->expects()->get('plugin_file')->andReturn('test3.php');

		$configs['test2']->expects()->getErrors()->andReturn([
			(object) [
				'errorMessage' => 'test',
				'info' => 'test'
			]
		]);

		$configs['test2']->expects()->get('plugin_file')->andReturn('test2.php');

		WP_Mock::userFunction('deactivate_plugins', ['times' => 1]);
		WP_Mock::passthruFunction('plugin_basename');
		WP_Mock::userFunction('wp_die', ['times' => 1]);
		WP_Mock::userFunction('wp_kses_post', ['times' => 1]);

		$jwlib = new JayWolfeLib($this->containerBuilder);
		$jwlib->setContainer($this->container);
		$jwlib->checkAndSetConfigs($collection);

		$this->assertTrue($this->container->has('config.test1.php'));
		$this->assertTrue($this->container->has('config.test3.php'));
		$this->assertFalse($this->container->has('config.test2.php'));
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

		$filter = Filter::create([
			Filter::HOOK => 'test',
			Filter::CALLABLE => function() {}
		]);

		WP_Mock::onAction('jwlib_hooks')
			->with($collection)
			->perform(function() use ($collection, $filter) {
				$collection->addFilter($filter);
			});

		$jwlib->init();

		$this->assertInstanceOf(HookInterface::class, $collection->getById($filter->id()));
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

		$post_type = new PostType('test');

		WP_Mock::onAction('jwlib_post_types')
			->with($collection)
			->perform(function() use ($collection, $post_type) {
				$collection->registerPostType($post_type);
			});

		$jwlib->init();

		$this->assertInstanceOf(PostTypeInterface::class, $collection->getById($post_type->id()));
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

		$mp = MenuPage::create([
			MenuPage::SLUG => 'test',
			MenuPage::CALLABLE => function() {}
		]);

		WP_Mock::onAction('jwlib_admin_menu')
			->with($collection)
			->perform(fn() => $collection->menuPage($mp));

		$jwlib->init();

		do_action('jwlib_admin_menu', $jwlib->getContainer()->get(MenuCollection::class));

		$this->assertInstanceOf(MenuPage::class, $collection->getById($mp->id()));
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

		$smp = SubMenuPage::create([
			SubMenuPage::SLUG => 'test',
			SubMenuPage::PARENT_SLUG => 'parent-test',
			SubMenuPage::CALLABLE => function() {}
		]);

		WP_Mock::onAction('jwlib_admin_menu')
			->with($collection)
			->perform(fn() => $collection->subMenuPage($smp));

		$jwlib->init();

		do_action('jwlib_admin_menu', $jwlib->getContainer()->get(MenuCollection::class));

		$this->assertInstanceOf(SubMenuPage::class, $collection->getById($smp->id()));
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

		$widget = new Widget(Mockery::mock(\WP_Widget::class));

		WP_Mock::onAction('jwlib_register_widgets')
			->with($collection)
			->perform(fn() => $collection->registerWidget($widget));

		$jwlib->init();

		do_action('jwlib_register_widgets', $jwlib->getContainer()->get(WidgetCollection::class));

		$this->assertInstanceOf(WidgetInterface::class, $collection->getById($widget->id()));
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

		$mb = MetaBox::create([
			MetaBox::META_ID => 'test',
			MetaBox::TITLE => 'test',
			MetaBox::CALLABLE => function() {}
		]);

		WP_Mock::onAction('jwlib_meta_boxes')
			->with($collection)
			->perform(fn() => $collection->addMetaBox($mb));

		$jwlib->init();

		do_action('jwlib_meta_boxes', $jwlib->getContainer()->get(MetaBoxCollection::class));

		$this->assertInstanceOf(MetaBoxInterface::class, $collection->getById($mb->id()));
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

		$shortcode = Shortcode::create([
			Shortcode::TAG => 'test',
			Shortcode::CALLABLE => function() {}
		]);

		WP_Mock::onAction('jwlib_shortcodes')
			->with($collection)
			->perform(fn() => $collection->addShortcode($shortcode));

		$jwlib->init();

		$this->assertInstanceOf(ShortcodeInterface::class, $collection->getById($shortcode->id()));
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