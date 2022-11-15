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

	public function render(string $_template, array $_args = [], ?string $_template_path = null)
	{
		extract($_args);

		$located = $this->locate_template($_template, $_template_path);

		if (null === $located) return;

		ob_start();
		Hooks::do_action('jwlib_before_template_render', $_template, $_template_path, $located, $_args);
		include $located;
		Hooks::do_action('jwlib_after_template_render', $_template, $_template_path, $located, $_args);

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