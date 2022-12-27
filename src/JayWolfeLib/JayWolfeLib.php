<?php

namespace JayWolfeLib;

use JayWolfeLib\Component\Config\ConfigCollection;
use JayWolfeLib\Component\Config\Config;
use JayWolfeLib\Component\WordPress\Filter\FilterCollection;
use JayWolfeLib\Component\WordPress\AdminMenu\MenuCollection;
use JayWolfeLib\Component\WordPress\Shortcode\ShortcodeCollection;
use JayWolfeLib\Component\WordPress\PostType\PostTypeCollection;
use JayWolfeLib\Traits\ContainerAwareTrait;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

final class JayWolfeLib
{
	use ContainerAwareTrait;

	/** @var bool */
	private static $loaded = false;

	/** @var ContainerBuilder */
	private $containerBuilder;

	public function __construct(ContainerBuilder $containerBuilder)
	{
		$this->containerBuilder = $containerBuilder;
	}

	public static function load(string $config_file = null): bool
	{
		try {
			if (null !== $config_file) {
				add_action('jwlib_config', function(ConfigCollection $configCollection) use ($config_file) {
					$config = Config::create($config_file);
					$configCollection->add( plugin_basename( $config->get('plugin_file') ), $config );
				});
			}

			if (did_action('init')) {
				throw new \BadMethodCallException(
					sprintf('%s must be called before "init"', __METHOD__)
				);
			}

			if (self::$loaded) {
				return true;
			}

			self::$loaded = add_action('init', function() {
				try {
					$instance = new self( new ContainerBuilder() );

					// Initialize the global container.
					$container = $instance->add_definitions();
					container( $container );

					do_action('jwlib_config', $container->get(ConfigCollection::class));
					do_action('jwlib_hooks', $container->get(FilterCollection::class));
					do_action('jwlib_post_types', $container->get(PostTypeCollection::class));
					add_action('admin_menu', function() use ($container) {
						do_action('jwlib_admin_menu', $container->get(MenuCollection::class));
					});
					do_action('jwlib_shortcodes', $container->get(ShortcodeCollection::class));

					do_action('jwlib_loaded', $container);
					unset($instance);
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

	public function add_definitions(): ContainerInterface
	{
		$this->containerBuilder->addDefinitions([
			Request::class => \DI\factory([Request::class, 'createFromGlobals']),
			\WPDB::class => function() {
				global $wpdb;
				return $wpdb;
			}
		]);

		do_action('jwlib_container_definitions', $this->containerBuilder);

		$this->set_container($this->containerBuilder->build());
		return $this->container;
	}
}
