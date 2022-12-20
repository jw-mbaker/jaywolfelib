<?php

namespace JayWolfeLib;

use JayWolfeLib\Exception\InvalidClass;
use DownShift\WordPress\EventEmitter;
use Symfony\Component\HttpFoundation\Request;

class Container extends \Pimple\Container
{
	public function get(string $id)
	{
		return $this->offsetGet($id);
	}

	public function set(string $id, $value)
	{
		return $this->offsetSet($id, $value);
	}

	public function init(string $id, string $class, ...$args)
	{
		if (!class_exists($class)) {
			throw new InvalidClass($class . ' not found.');
		}

		if (!isset($this[$id])) {
			$this->set($id, new $class(...$args));
		}

		if (method_exists($this[$id], 'init')) {
			$this[$id]->init();
		}

		return $this[$id];
	}

	public function flush()
	{
		foreach ($this->keys() as $key) {
			$this->offsetUnset($key);
		}
	}

	/**
	 * Bootstrap the global container.
	 *
	 * @param Container $container
	 * @return void
	 */
	public static function bootstrap(Container $container)
	{
		if (!isset($container['hooks'])) {
			$container->set('hooks', new EventEmitter());
		}

		if (!isset($container['request'])) {
			$container->set('request', fn() => Request::createFromGlobals());
		}

		if (!isset($container['config'])) {
			$container->set('config', new Config\Factory(new self(), $container));
		}

		if (!isset($container['guzzle'])) {
			$container->set('guzzle', fn() => new Guzzle\Factory());
		}

		if (!isset($container['wpdb'])) {
			$container->set('wpdb', function(Container $c) {
				global $wpdb;
				return $wpdb;
			});
		}

		if (!isset($container['models'])) {
			$container->set('models', new Models\Factory(new self(), $container));
		}
		
		if (!isset($container['controllers'])) {
			$container->set('controllers', new Controllers\Factory(new self(), $container));
		}

		if (!isset($container['api'])) {
			$container->set('api', fn(Container $c) => new Api\Api($c->get('request')));
		}
	}
}