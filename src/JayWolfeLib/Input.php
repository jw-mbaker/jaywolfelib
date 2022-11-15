<?php

namespace JayWolfeLib;

use JayWolfeLib\Exception\InvalidClass;

class Input
{
	/**
	 * The filters.
	 *
	 * @var array
	 */
	protected $filters = ['stripslashes'];

	public function read(array $input_array, $param_name, $default = null)
	{
		if (is_array($param_name) && !is_null($default)) {
			throw new \Exception('Either array of parameter names with default values as the only argument or param name and default value as separate arguments are expected.');
		}

		if (is_array($param_name)) {
			foreach ($param_name as $param => $def) {
				if (isset($input_array[$param])) {
					$param_name[$param] = $this->apply_filters($input_array[$param]);
				}
			}

			return $param_name;
		}

		return isset($input_array[$param_name]) ? $this->apply_filters($input_array[$param_name]) : $default;
	}

	public function get($param_name, $default = null)
	{
		$this
			->add_filter('strip_tags')
			->add_filter('htmlspecialchars')
			->add_filter('esc_sql')
			->add_filter('esc_js');
		$result = $this->read($_GET, $param_name, $default);
		$this
			->remove_filter('strip_tags')
			->remove_filter('htmlspecialchars')
			->remove_filter('esc_sql')
			->remove_filter('esc_js');

		return $result;
	}

	public function post($param_name, $default = null)
	{
		return $this->read($_POST, $param_name, $default);
	}

	public function cookie($param_name, $default = null)
	{
		return $this->read($_COOKIE, $param_name, $default);
	}

	public function request($param_name, $default = null)
	{
		return $this->read($_GET + $_POST + $_COOKIE, $param_name, $default);
	}

	public function getpost($param_name, $default = null)
	{
		return $this->read($_GET + $_POST, $param_name, $default);
	}

	public function server($param_name, $default = null)
	{
		return $this->read($_SERVER, $param_name, $default);
	}

	public function add_filter(callable $callback): self
	{
		if (!in_array($callback, $this->filters)) {
			$this->filters[] = $callback;
		}

		return $this;
	}

	public function remove_filter(callable $callback): self
	{
		$this->filters = array_diff($this->filters, [$callback]);
		return $this;
	}

	protected function apply_filters($val)
	{
		if (is_array($val)) {
			foreach ($val as $k => $v) {
				$val[$k] = $this->apply_filters($v);
			}
		} else {
			foreach ($this->filters as $filter) {
				$val = call_user_func($filter, $val);
			}
		}

		return $val;
	}

	public function send_json($response, int $status_code = null, int $options = 0)
	{
		wp_send_json($response, $status_code, $options);
	}
}