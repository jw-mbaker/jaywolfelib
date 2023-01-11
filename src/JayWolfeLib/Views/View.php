<?php declare(strict_types=1);

namespace JayWolfeLib\Views;

use JayWolfeLib\Config\ConfigInterface;
use JayWolfeLib\Config\ConfigTrait;
use JayWolfeLib\Exception\InvalidTemplateException;

class View implements ViewInterface
{
	use ConfigTrait;

	public function __construct(?ConfigInterface $config = null)
	{
		if (null !== $config) {
			$this->setConfig($config);
		}
	}
	
	/**
	 * Render the template.
	 *
	 * @param string $_template
	 * @param array $_args
	 * @param string|null $_templatePath
	 * @return string
	 */
	public function render(string $_template, array $_args = [], ?string $_templatePath = null): string
	{
		extract($_args);

		$located = $this->locateTemplate($_template, $_templatePath);

		if (null === $located) return '';

		ob_start();
		do_action('jwlib_before_template_render', $_template, $_templatePath, $located, $_args);
		include $located;
		do_action('jwlib_after_template_render', $_template, $_templatePath, $located, $_args);

		return ob_get_clean();
	}
	
	/**
	 * Locate the template file.
	 *
	 * @param string $template
	 * @param string|null $templatePath
	 * @return string|null
	 * @throws InvalidTemplateException
	 */
	protected function locateTemplate(string $template, ?string $templatePath = null): ?string
	{
		if (null === $templatePath && !$this->config instanceof ConfigInterface) {
			throw new \InvalidArgumentException(
				sprintf('$templatePath must be specified if %s is not provided.', ConfigInterface::class)
			);
		}

		if ($this->config instanceof ConfigInterface) {
			if (empty($this->config->get('paths')['templates'])) {
				throw new InvalidTemplateException('Template path not set for ' . $this->config->get('plugin_file') . '.');
			}
	
			$templatePath ??= $this->config->get('paths')['templates'];
		}

		if (!preg_match('/\.php/', $template)) {
			$template .= '.php';
		}

		$filePath = trailingslashit( $templatePath ) . $template;

		if (file_exists($filePath)) {
			return $filePath;
		}

		return null;
	}
}