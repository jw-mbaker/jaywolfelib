<?php

namespace JayWolfeLib\Route;

use JayWolfeLib\Factory\ControllerFactoryInterface;
use JayWolfeLib\Factory\BaseFactoryInterface;
use JayWolfeLib\Config\ConfigInterface;
use JayWolfeLib\Views\View;
use JayWolfeLib\Hooks\Hooks;
use JayWolfeLib\Container;

class Router
{
	/** @var ControllerFactoryInterface */
	protected $controllers;

	/** @var ConfigInterface */
	protected $config;

	private $components = [];

	/**
	 * The current controller being registered.
	 *
	 * @var string
	 */
	protected $current_controller;

	/**
	 * The route type to register.
	 *
	 * @var string
	 */
	public $route_type_to_register;

	public function __construct(ControllerFactoryInterface $controllers, ConfigInterface $config)
	{
		$this->controllers = $controllers;
		$this->config = $config;
		$this->init();
	}

	protected function init()
	{
		Hooks::add_action('init', [$this, 'register_generic_routes']);
		Hooks::add_action('wp', [$this, 'register_late_frontend_routes']);
	}

	public function register_generic_routes(): void
	{
		$this->register_routes();

		Hooks::do_action('jwlib_generic_routes_registered', $this->controllers, $this->config);
	}

	public function register_late_frontend_routes(): void
	{
		$this->register_routes(true);

		Hooks::do_action('jwlib_late_frontend_routes_registered', $this->controllers, $this->config);
	}

	public function generic_route_types(): array
	{
		return [
			RouteType::ANY,
			RouteType::API,
			RouteType::ADMIN,
			RouteType::ADMIN_WITH_POSSIBLE_AJAX,
			RouteType::AJAX,
			RouteType::CRON,
			RouteType::FRONTEND,
			RouteType::FRONTEND_WITH_POSSIBLE_AJAX
		];
	}

	public function late_frontend_route_types(): array
	{
		return [
			RouteType::LATE_FRONTEND,
			RouteType::LATE_FRONTEND_WITH_POSSIBLE_AJAX
		];
	}

	public function register_route_of_type(string $type): self
	{
		if (in_array($type, $this->late_frontend_route_types()) && did_action('wp')) {
			trigger_error( __( 'Late Routes can not be registered after `wp` hook is triggered. Register your route before `wp` hook is triggered.', 'JayWolfeLib' ), E_USER_ERROR );
		}

		if (in_array($type, $this->generic_route_types()) && did_action('init')) {
			trigger_error( __( 'Non-Late Routes can not be registered after `init` hook is triggered. Register your route before `init` hook is triggered.', 'JayWolfeLib' ), E_USER_ERROR );
		}

		$this->route_type_to_register = $type;
		return $this;
	}

	public function with_controller($controller): self
	{
		if (false === $controller) {
			return $this;
		}

		$this->current_controller = $this->build_controller_unique_id($controller);

		$this->components[$this->route_type_to_register][$this->current_controller] = ['controller' => $controller];

		return $this;
	}

	public function with_model($model): self
	{
		return $this->with_dependency($model);
	}

	public function with_dependency($dependency): self
	{
		if (isset($this->components[$this->route_type_to_register][$this->current_controller]['controller'])) {
			$this->components[$this->route_type_to_register][$this->current_controller]['dependencies'] ??= [];
			$this->components[$this->route_type_to_register][$this->current_controller]['dependencies'][] = $dependency;
		}

		return $this;
	}

	public function with_view($view): self
	{
		if (isset($this->components[$this->route_type_to_register][$this->current_controller]['controller'])) {
			$this->components[$this->route_type_to_register][$this->current_controller]['view'] = $view;
		}

		return $this;
	}

	public function with_api_key(string $api_key): self
	{
		if ($this->route_type_to_register !== RouteType::API) {
			return $this;
		}

		if (isset($this->components[$this->route_type_to_register][$this->current_controller]['controller'])) {
			$this->components[$this->route_type_to_register][$this->current_controller]['api_key'] = $api_key;
		}

		return $this;
	}

	public function build_controller_unique_id($controller): string
	{
		$prefix = mt_rand() . '_';

		if (is_string($controller)) {
			return $prefix . $controller;
		}

		if (is_object($controller)) {
			$controller = array( $controller, '' );
		} else {
			$controller = (array) $controller;
		}

		if (is_object($controller[0])) {
			return $prefix . spl_object_hash($controller[0]) . $controller[1];
		}

		if (is_string($controller[0])) {
			return $prefix . $controller[0] . '::' . $controller[1];
		}
	}

	private function register_routes(bool $register_late_frontend_routes = false)
	{
		if ($register_late_frontend_routes) {
			$route_types = $this->late_frontend_route_types();
		} else {
			$route_types = $this->generic_route_types();
		}

		if (empty($route_types)) {
			return;
		}

		foreach ($route_types as $route_type) {
			if ($this->is_request($route_type) && !empty($this->components[$route_type])) {
				foreach ($this->components[$route_type] as $component) {
					$this->dispatch($component, $route_type);
				}
			}
		}
	}

	private function dispatch(array $component, string $route_type)
	{
		$dependencies = [];

		if (isset($component['controller']) && false === $component['controller']) {
			return;
		}

		if (is_callable($component['controller'])) {
			$component['controller'] = call_user_func($component['controller']);

			if (false === $component['controller']) {
				return;
			}
		}

		if (isset($component['view'])) {
			if (is_callable($component['view'])) {
				$component['view'] = call_user_func($component['view']);
			}

			if (class_exists($component['view'])) {
				$view = new $component['view']($this->config);
			}
		}

		$view ??= new View($this->config);

		if (isset($component['dependencies'])) {
			foreach ($component['dependencies'] as $dependency) {
				if (is_string($dependency) && class_exists($dependency)) {
					$dependency = new $dependency();
				} elseif (is_array($dependency) && ($dependency[0] instanceof Container || $dependency[0] instanceof BaseFactoryInterface)) {
					$dependency = $dependency[0]->get($dependency[1]);
				} elseif (is_callable($dependency)) {
					$dependency = call_user_func($dependency);
				}

				$dependencies[] = $dependency;
			}
		}

		@list($controller, $action) = explode('@', $component['controller']);

		$controller = $this->controllers->create($controller, $view, ...$dependencies);

		if (null !== $action) {
			$contoller->$action();
		}
	}

	/**
	 * Identifies request type.
	 *
	 * @param string $route_type
	 * @return bool
	 */
	private function is_request(string $route_type): bool
	{
		switch ($route_type) {
			case RouteType::ANY:
				return true;
			case RouteType::ADMIN:
			case RouteType::ADMIN_WITH_POSSIBLE_AJAX:
				return is_admin();
			case RouteType::AJAX:
				return defined( 'DOING_AJAX' );
			case RouteType::CRON:
				return defined( 'DOING_CRON' ) || defined( 'JW_DOING_CRON' );
			case RouteType::FRONTEND:
			case RouteType::FRONTEND_WITH_POSSIBLE_AJAX:
				return (!is_admin() || defined( 'DOING_AJAX' )) && !defined( 'DOING_CRON' ) && !defined( 'REST_REQUEST' );
			case RouteType::API:
				return !is_admin() && !defined( 'DOING_AJAX' ) && !defined( 'DOING_CRON' ) && !defined( 'REST_REQUEST' );
			case RouteType::LATE_FRONTEND:
			case RouteType::LATE_FRONTEND_WITH_POSSIBLE_AJAX:
				return $this->is_request(RouteType::FRONTEND) || ( current_action() == 'wp' ) || ( did_action('wp') === 1 );
		}
	}

	public function get_components(): array
	{
		return $this->components;
	}
}