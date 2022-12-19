<?php

namespace JayWolfeLib;

use JayWolfeLib\Hooks\Hooks;
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
use DownShift\WordPress\EventEmitterInterface;
use DownShift\WordPress\EventEmitter;
use Symfony\Component\HttpFoundation\Request;

$containerBuilder = new ContainerBuilder();
if (defined("JayWolfeLib\\PRODUCTION") && PRODUCTION) {
	$containerBuilder->enableCompilation(__DIR__ . '/cache');
}

$containerBuilder->addDefinitions([
	EventEmitterInterface::class => \DI\create(EventEmitter::class),
	Request::class => \DI\factory([Request::class, 'createFromGlobals']),
	ConfigCollection::class => \DI\create(),
	GuzzleFactoryInterface::class => \DI\create(GuzzleFactory::class),
	\WPDB::class => function() {
		global $wpdb;
		return $wpdb;
	},
	ModelCollection::class => \DI\create(),
	ModelFactoryInterface::class => \DI\create(ModelFactory::class)
		->constructor(\DI\get(ModelCollection::class), \DI\get(Container::class)),
	ControllerCollection::class => \DI\create(),
	ControllerFactoryInterface::class => \DI\create(ControllerFactory::class)
		->constructor(\DI\get(ControllerCollection::class)),
	RouteCollection::class => \DI\create()
]);

$container;

add_action('plugins_loaded', function() use (&$container, $containerBuilder) {
	$defintions = apply_filters('jwlib_add_container_definitions', $containerBuilder);
	$container = $containerBuilder->build();

	$routeCollection = apply_filters('jwlib_add_routes', $container->get(RouteCollection::class));

	if (count($routeCollection) == 0) return;

	$dispatcher = new Dispatcher($routeCollection);
}, 999, 1);

if (is_admin()) {
	add_action('admin_menu', function() use ($container) {
		$adminMenuPages = apply_filters('jwlib_admin_menu', []);
	});
}

add_filter('jwlib_get_container', fn() => $container);