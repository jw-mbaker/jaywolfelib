<?php

namespace JayWolfeLib;

use JayWolfeLib\Config\ConfigCollection;
use JayWolfeLib\Factory\GuzzleFactoryInterface;
use JayWolfeLib\Factory\ModelFactoryInterface;
use JayWolfeLib\Factory\ControllerFactoryInterface;
use JayWolfeLib\Guzzle\Factory as GuzzleFactory;
use JayWolfeLib\Models\ModelCollection;
use JayWolfeLib\Models\Factory as ModelFactory;
use JayWolfeLib\Controllers\ControllerCollection;
use JayWolfeLib\Controllers\Factory as ControllerFactory;
use JayWolfeLib\Route\RouteCollection;
use JayWolfeLib\Route\Dispatcher;
use DI\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;

class Init
{
	private $containerBuilder;
	private $container;

	public function __construct(ContainerBuilder $containerBuilder)
	{
		$this->containerBuilder = $containerBuilder;
	}

	public function run()
	{
		add_action('init', [$this, 'add_definitions'], 10, 1);
		add_action('init', [$this, 'register_routes'], 15, 1);

		if (is_admin()) {
			add_action('admin_menu', [$this, 'admin_menu']);
		}
	}

	public function add_definitions()
	{
		$this->containerBuilder->addDefinitions([
			Request::class => \DI\factory([Request::class, 'createFromGlobals']),
			ConfigCollection::class => \DI\create(),
			GuzzleFactoryInterface::class => \DI\create(GuzzleFactory::class),
			\WPDB::class => function() {
				global $wpdb;
				return $wpdb;
			},
			ModelCollection::class => \DI\create(),
			ModelFactoryInterface::class => function(Container $c) {
				return new ModelFactory($c->get(ModelCollection::class), $c);
			},
			ControllerCollection::class => \DI\create(),
			ControllerFactoryInterface::class => \DI\create(ControllerFactory::class)
				->constructor(\DI\get(ControllerCollection::class)),
			RouteCollection::class => \DI\create()
		]);

		do_action('jwlib_container_definitions', $this->containerBuilder);

		$this->container = $this->containerBuilder->build();

		add_filter('jwlib_get_container', fn() => $this->container);
	}

	public function register_routes()
	{
		do_action('jwlib_routes', $this->container->get(RouteCollection::class));
	}

	public function admin_menu()
	{
		do_action('jwlib_admin_menu', new AdminMenu($this->container));
	}
}