<?php

namespace JayWolfeLib\Views;

use JayWolfeLib\Component\Config\ConfigInterface;

class ViewFactory
{
	public function make(
		string $template,
		array $args,
		string $template_path = null,
		ConfigInterface $config = null
	): string {
		$view = new View($config);
		return $view->render($template, $args, $template_path);
	}
}