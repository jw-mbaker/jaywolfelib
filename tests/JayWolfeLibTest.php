<?php

namespace JayWolfeLib\Tests;

use JayWolfeLib\JayWolfeLib;
use JayWolfeLib\Component\Config\ConfigCollection;
use JayWolfeLib\Component\Config\ConfigInterface;
use JayWolfeLib\Component\WordPress\Filter\FilterCollection;
use JayWolfeLib\Component\WordPress\Filter\HookInterface;
use JayWolfeLib\Tests\Traits\DevContainerTrait;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use WP_Mock;
use Mockery;

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
		JayWolfeLib::$loaded = false;
	}

	public function testEnableCompilation()
	{
		WP_Mock::onFilter('jwlib_dev')
			->with(defined('JAYWOLFE_LIB_DEV'))
			->reply(false);

		$jwlib = new JayWolfeLib($this->containerBuilder);
		$container = $jwlib->add_definitions();

		$this->assertInstanceOf(ContainerInterface::class, $container);
		$this->assertTrue(class_exists(\JwLibCompiledContainer::class));
		$this->assertFileExists(JAYWOLFE_LIB_CACHE_DIR . '/JwLibCompiledContainer.php');

		unlink(JAYWOLFE_LIB_CACHE_DIR . '/JwLibCompiledContainer.php');
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
}