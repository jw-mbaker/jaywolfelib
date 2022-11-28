<?php

namespace JayWolfeLib\Config;

interface ConfigInterface
{
	public function set(string $key, $val);
	public function get(string $key);
	public function get_settings(): array;
	public function requirements_met(): bool;
	public function get_errors(): array;
}