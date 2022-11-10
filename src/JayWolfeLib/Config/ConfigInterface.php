<?php

namespace JayWolfeLib\Config;

interface ConfigInterface
{
	public function set(string $key, $val);
	public function get(string $key);
	public function get_config(): array;
}