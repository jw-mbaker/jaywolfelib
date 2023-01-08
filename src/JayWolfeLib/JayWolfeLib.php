<?php declare(strict_types=1);

namespace JayWolfeLib;

use JayWolfeLib\Component\Config\ConfigCollection;
use JayWolfeLib\Component\Config\ConfigInterface;
use JayWolfeLib\Component\Config\Config;
use JayWolfeLib\Component\WordPress\Filter\FilterCollection;
use JayWolfeLib\Component\WordPress\AdminMenu\MenuCollection;
use JayWolfeLib\Component\WordPress\Shortcode\ShortcodeCollection;
use JayWolfeLib\Component\WordPress\PostType\PostTypeCollection;
use JayWolfeLib\Component\WordPress\Widget\WidgetCollection;
use JayWolfeLib\Component\WordPress\MetaBox\MetaBoxCollection;
use JayWolfeLib\Traits\ContainerAwareTrait;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

final class JayWolfeLib
{
	use ContainerAwareTrait;

	private static bool $loaded = false;

	private ContainerBuilder $containerBuilder;

	public function __construct(ContainerBuilder $containerBuilder)
	{
		$this->containerBuilder = $containerBuilder;
	}

	public static function load(string $config_file = null, ContainerBuilder $containerBuilder = null): bool
	{
		try {
			if (null !== $config_file) {
				add_action('jwlib_config', function(ConfigCollection $configCollection) use ($config_file) {
					$config = Config::from_file($config_file);
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

			add_action('jwlib_container_definitions', function(ContainerBuilder $containerBuilder) {
				$containerBuilder->useAnnotations(true);
			}, -1, 1);

			add_action('init', [new self($containerBuilder ?? new ContainerBuilder()), 'init'], 0, 1);
		} catch (\Exception $e) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				throw $e;
			}

			do_action('jwlib_fail', $e);
		}

		$dev = apply_filters('jwlib_dev', !defined(__NAMESPACE__ . '\\PRODUCTION') || !PRODUCTION);

		if ($dev === false) {
			self::$loaded = true;
		}

		return true;
	}

	public function init()
	{
		try {
			// Initialize the global container.
			$container = $this->add_definitions();
			//container( $container );

			add_action('jwlib_config', [$this, 'check_and_set_configs'], 99, 1);

			do_action('jwlib_config', $container->get(ConfigCollection::class));
			do_action('jwlib_hooks', $container->get(FilterCollection::class));
			do_action('jwlib_post_types', $container->get(PostTypeCollection::class));
			add_action('admin_menu', function() use ($container) {
				do_action('jwlib_admin_menu', $container->get(MenuCollection::class));
			});
			add_action('widgets_init', function() use ($container) {
				do_action('jwlib_register_widgets', $container->get(WidgetCollection::class));
			});
			add_action('add_meta_boxes', function() use ($container) {
				do_action('jwlib_meta_boxes', $container->get(MetaBoxCollection::class));
			});
			do_action('jwlib_shortcodes', $container->get(ShortcodeCollection::class));

			do_action('jwlib_loaded', $container);
		} catch (\Exception $e) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				throw $e;
			}

			do_action('jwlib_fail', $e);
			return;
		}
	}

	public function add_definitions(): ContainerInterface
	{
		$dev = apply_filters('jwlib_dev', !defined(__NAMESPACE__ . '\\PRODUCTION') || !PRODUCTION);

		if ($dev === false) {
			$this->containerBuilder->enableCompilation(
				CACHE_DIR,
				"JwLibCompiledContainer"
			);
		}

		$this->containerBuilder->addDefinitions([
			\WPDB::class => function() {
				global $wpdb;
				return $wpdb;
			}
		]);

		do_action('jwlib_container_definitions', $this->containerBuilder);

		$this->set_container($this->containerBuilder->build());
		return $this->container;
	}

	public function check_and_set_configs(ConfigCollection $configs)
	{
		foreach ($configs as $config) {
			if ($this->check_requirements($config)) {
				$this->container->set(sprintf('config.%s', plugin_basename($config->get('plugin_file'))), $config);
			}
		}
	}

	private function check_requirements(ConfigInterface $config): bool
	{
		if (!$config->requirements_met()) {
			ob_start();
			$errors = $config->get_errors();
			foreach ($errors as $error):
			?>
			<div><?=$error->error_message?> (<?=$error->info?>)</div>
			<?php
			endforeach;

			$this->deactivate_die($config->get('plugin_file'), ob_get_clean());

			return false;
		}

		return true;
	}

	private function deactivate_die(string $plugin_file, string $message)
	{
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		deactivate_plugins( plugin_basename( $plugin_file ) );

		wp_die( wp_kses_post($message) );
	}
}
