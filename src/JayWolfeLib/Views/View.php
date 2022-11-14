<?php

namespace JayWolfeLib\Views;

use JayWolfeLib\Config\ConfigInterface;
use JayWolfeLib\Hooks\Hooks;

class View implements ViewInterface
{
	/** @var ConfigInterface */
	protected $config;

	/**
	 * Constructor.
	 *
	 * @param ConfigInterface $config
	 */
	public function __construct(ConfigInterface $config)
	{
		$this->config = $config;
	}

	public function render(string $template, array $args = [], ?string $template_path = null)
	{
		extract($args);

		$located = $this->locate_tempate($template, $template_path);

		if (null === $located) return;

		ob_start();
		Hooks::do_action('jwlib_before_template_render', $template, $template_path, $located, $args);
		include $located;
		Hooks::do_action('jwlib_after_template_render', $template, $template_path, $located, $args);

		echo ob_get_clean();
	}
	
	protected function locate_template(string $template, ?string $template_path = null): ?string
	{
		$template_path ??= $this->config->get('paths')['templates'];

		if (!preg_match('/\.php/', $template)) {
			$template .= '.php';
		}

		$file_path = trailingslashit( $template_path ) . $template;

		if (file_exists($file_path)) {
			return $file_path;
		}

		return null;
	}
}