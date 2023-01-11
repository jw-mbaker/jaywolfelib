<?php declare(strict_types=1);

namespace JayWolfeLib\Views;

use JayWolfeLib\Config\ConfigInterface;

class ViewFactory
{
	public function make(
		string $template,
		array $args = [],
		?string $templatePath = null,
		?ConfigInterface $config = null
	): string {
		$view = new View($config);
		return $view->render($template, $args, $templatePath);
	}
}