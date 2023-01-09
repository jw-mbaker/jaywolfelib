<?php declare(strict_types=1);

namespace JayWolfeLib\Views;

use JayWolfeLib\Component\Config\ConfigInterface;
use JayWolfeLib\Component\Config\ConfigTrait;
use JayWolfeLib\Hooks\Hooks;
use JayWolfeLib\Exception\InvalidTemplate;

class View implements ViewInterface
{
	use ConfigTrait;

	public function __construct(ConfigInterface $config = null)
	{
		if (null !== $config) {
			$this->set_config($config);
		}
	}
	
	/**
	 * Render the template.
	 *
	 * @param string $_template
	 * @param array $_args
	 * @param string|null $_template_path
	 * @return string
	 */
	public function render(string $_template, array $_args = [], string $_template_path = null): string
	{
		extract($_args);

		$located = $this->locate_template($_template, $_template_path);

		if (null === $located) return '';

		ob_start();
		do_action('jwlib_before_template_render', $_template, $_template_path, $located, $_args);
		include $located;
		do_action('jwlib_after_template_render', $_template, $_template_path, $located, $_args);

		return ob_get_clean();
	}
	
	/**
	 * Locate the template file.
	 *
	 * @param string $template
	 * @param string|null $template_path
	 * @return string|null
	 * @throws InvalidTemplate
	 */
	protected function locate_template(string $template, ?string $template_path = null): ?string
	{
		if (null === $template_path && !$this->config instanceof ConfigInterface) {
			throw new \InvalidArgumentException(
				sprintf('$template_path must be specified if %s is not provided.', ConfigInterface::class)
			);
		}

		if ($this->config instanceof ConfigInterface) {
			if (empty($this->config->get('paths')['templates'])) {
				throw new InvalidTemplate('Template path not set for ' . $this->config->get('plugin_file') . '.');
			}
	
			$template_path ??= $this->config->get('paths')['templates'];
		}

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