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
use JayWolfeLib\Hooks\AdminMenu;
use JayWolfeLib\Routing\RouteCollectionInterface;
use JayWolfeLib\Routing\RouteCollection;
use JayWolfeLib\Routing\RouterInterface;
use JayWolfeLib\Routing\Router;
use JayWolfeLib\Routing\AjaxRouter;
use JayWolfeLib\Routing\ApiRouter;
use JayWolfeLib\Handler\ResultHandler;
use JayWolfeLib\Handler\ApiHandler;
use JayWolfeLib\Handler\AjaxHandler;
use GuzzleHttp\Psr7\Uri;
use DI\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;

class JayWolfeLib
{
	private static $loaded = false;

	private $containerBuilder;
	private $container;

	public function __construct(ContainerBuilder $containerBuilder)
	{
		$this->containerBuilder = $containerBuilder;
	}

	public static function load(Request $request = null): bool
	{
		try {
			if (self::$loaded) {
				return false;
			}

			if (did_action('init')) {
				throw new \BadMethodCallException(
					sprintf('%s must be called before "init"', __METHOD__)
				);
			}

			self::$loaded = add_action('init', function() use ($request) {
				try {
					$instance = new static( new ContainerBuilder(Container::class) );

					// Initialize the global container.
					container( $instance->add_definitions() );

					$instance->register_routes();
					

					unset($instance);

					do_action('jwlib_loaded');
				} catch (\Exception $e) {
					if (defined('WP_DEBUG') && WP_DEBUG) {
						throw $e;
					}

					do_action('jwlib_fail', $e);
					return;
				}
			}, 100, 1);
		} catch (\Exception $e) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				throw $e;
			}

			do_action('jwlib_fail', $e);
		}

		return true;
	}






	public function run()
	{
		add_action('init', [$this, 'add_definitions'], 10, 1);
		add_action('init', [$this, 'register_routes'], 15, 1);

		if (is_admin()) {
			add_action('admin_menu', [$this, 'admin_menu']);
		}
	}

	public function add_definitions(): Container
	{
		$this->containerBuilder->addDefinitions([
			Request::class => \DI\factory([Request::class, 'createFromGlobals']),
			ConfigCollection::class => \DI\create(),
			GuzzleFactoryInterface::class => \DI\create(GuzzleFactory::class),
			\WPDB::class => function() {
				global $wpdb;
				return $wpdb;
			},
			ModelFactoryInterface::class => \DI\create(ModelFactory::class),
			RouteCollectionInterface::class => \DI\create(RouteCollection::class)
		]);

		do_action('jwlib_container_definitions', $this->containerBuilder);

		$this->container = $this->containerBuilder->build();
		return $this->container;
	}

	public function register_routes()
	{
		do_action('jwlib_routes', $this->container->get(RouteCollectionInterface::class));

		$request = $this->container->get(Request::class);

		switch (true) {
			case (is_admin()):
				$router = $this->container->get(AjaxRouter::class);
				break;
			case (str_starts_with($request->getPathInfo(), '/api')):
				$router = $this->container->get(ApiRouter::class);
				break;
			default:
				$router = $this->container->get(Router::class);
		}

		$this->handle_request($router, $request);
	}

	public function admin_menu()
	{
		do_action('jwlib_admin_menu', new AdminMenu($this->container));
	}

	private function handle_request(RouterInterface $router, Request $request)
	{
		if ($router instanceof AjaxRouter) {
			$this->handle_admin($router, $request);
			return;
		}

		if ($router instanceof ApiRouter) {
			$this->handle_api($router, $request);
			return;
		}

		if ($router instanceof Router) {
			add_filter('do_parse_request', function(bool $do, \WP $wp) use ($router, $request) {
				return $this->parse_request($router, $request, $do, $wp);
			}, 999, 2);
		}
	}

	private function parse_request(RouterInterface $router, Request $request, bool $do, \WP $wp): bool
	{
		$do = $this->container->call(
			[ResultHandler::class, 'handle'],
			[$router->match(new Uri($request->getUri()), $request->getMethod())],
			$do
		);
		
		if (!$do) {
			global $wp_version;

			if ($wp_version && version_compare($wp_version, '6', '>=')) {
				$wp->query_posts();
				$wp->register_globals();
			}
		}

		return $do;
	}

	private function handle_ajax(RouterInterface $router, Request $request)
	{
		$this->container->call(
			[AjaxHandler::class, 'handle'],
			[$router->match($request->getMethod())]
		);
	}

	private function handle_api(RouterInterface $router, Request $request)
	{
		$this->container->call(
			[ApiHandler::class, 'handle'],
			[$router->match(new Uri($request->getUri())), $request->getMethod()]
		);
	}
}