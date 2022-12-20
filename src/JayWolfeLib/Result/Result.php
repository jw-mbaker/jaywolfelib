<?php

namespace JayWolfeLib\Routing;

class Result implements ResultInterface
{
	use ResultTrait;

	public function __construct(array $data = [])
	{
		$defaults = [
			'route' => null,
			'path' => null,
			'vars' => null,
			'matches' => null,
			'dependencies' => null,
			'template' => null
		];

		$this->data = array_merge($defaults, array_change_key_case($data, CASE_LOWER));
	}

	/**
	 * @return string|bool|callable
	 */
	public function template()
	{
		$template = $this->data['template'];

		return (is_string($template) || $template === false || is_callable($template))
			? $template
			: '';
	}
}