<?php

namespace JayWolfeLib\Views;

use JayWolfeLib\Component\Config\ConfigInterface;

interface ViewInterface
{
	public function render(
		string $template,
		array $args = [],
		string $template_path = null
	): string;

	public function set_config(ConfigInterface $config);
	public function get_config(): ConfigInterface;
}