<?php declare(strict_types=1);

namespace JayWolfeLib;

use JayWolfeLib\Config\ConfigCollection;
use JayWolfeLib\Config\ConfigInterface;
use JayWolfeLib\Config\Config;
use JayWolfeLib\WordPress\Filter\FilterCollection;
use JayWolfeLib\WordPress\AdminMenu\MenuCollection;
use JayWolfeLib\WordPress\Shortcode\ShortcodeCollection;
use JayWolfeLib\WordPress\PostType\PostTypeCollection;
use JayWolfeLib\WordPress\Widget\WidgetCollection;
use JayWolfeLib\WordPress\MetaBox\MetaBoxCollection;
use JayWolfeLib\Contracts\ContainerAwareInterface;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

final class JayWolfeLib implements ContainerAwareInterface
{
	private static bool $loaded = false;

	private ContainerBuilder $containerBuilder;
	private ContainerInterface $container;

	public function __construct(ContainerBuilder $containerBuilder)
	{
		$this->containerBuilder = $containerBuilder;
	}

	public function setContainer(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getContainer(): ContainerInterface
	{
		return $this->container;
	}

	public static function load(?string $configFile = null, ?ContainerBuilder $containerBuilder = null): bool
	{
		try {
			if (null !== $configFile) {
				add_action('jwlib_container_definitions', function(ContainerBuilder $builder) use ($configFile) {
					$builder->addDefinitions([
						ConfigCollection::class => \DI\decorate(function($previous, ContainerInterface $c) use ($configFile) {
							$config = Config::fromFile($configFile);
							$previous->add( plugin_basename( $config->get('plugin_file') ), $config );
						})
					]);
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
			$container = $this->addDefinitions();
			//container( $container );

			add_action('jwlib_config', [$this, 'checkAndSetConfigs'], 99, 1);

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

	public function addDefinitions(): ContainerInterface
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
			},
			ConfigCollection::class => \DI\create()
		]);

		do_action('jwlib_container_definitions', $this->containerBuilder);

		$this->setContainer($this->containerBuilder->build());
		return $this->container;
	}

	public function checkAndSetConfigs(ConfigCollection $configs)
	{
		foreach ($configs as $config) {
			if ($this->checkRequirements($config)) {
				$this->container->set(sprintf('config.%s', plugin_basename($config->get('plugin_file'))), $config);
			}
		}
	}

	private function checkRequirements(ConfigInterface $config): bool
	{
		if (!$config->requirementsMet()) {
			ob_start();
			$errors = $config->getErrors();
			foreach ($errors as $error):
			?>
			<div><?=$error->errorMessage?> (<?=$error->info?>)</div>
			<?php
			endforeach;

			$this->deactivateDie($config->get('plugin_file'), ob_get_clean());

			return false;
		}

		return true;
	}

	private function deactivateDie(string $plugin_file, string $message)
	{
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		deactivate_plugins( plugin_basename( $plugin_file ) );

		wp_die( wp_kses_post($message) );
	}
}
