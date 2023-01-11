<?php declare(strict_types=1);

namespace JayWolfeLib\Views;

use JayWolfeLib\Config\ConfigInterface;

interface ViewInterface
{
	public function render(
		string $template,
		array $args = [],
		?string $templatePath = null
	): string;

	public function setConfig(ConfigInterface $config);
	public function getConfig(): ConfigInterface;
}