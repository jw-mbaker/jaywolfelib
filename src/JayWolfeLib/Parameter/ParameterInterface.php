<?php declare(strict_types=1);

namespace JayWolfeLib\Parameter;

interface ParameterInterface extends \IteratorAggregate, \Countable
{
	public function clear();
	public function add(array $parameters = []);
	public function all();
	public function get(string $key, $default = null);
	public function remove(string $key);
	public function replace(array $parameters = []);
	public function set(string $key, $value);
	public function has(string $key): bool;
}
