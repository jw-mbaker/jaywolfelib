<?php

namespace JayWolfeLib\Component;

interface ParameterInterface
{
	public function clear();
	public function add(array $parameters);
	public function all();
	public function get(string $name);
	public function remove(string $name);
	public function set(string $name, $value);
	public function has(string $name);
}
