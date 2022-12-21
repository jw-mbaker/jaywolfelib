<?php

namespace JayWolfeLib\Component\Routing;

class AjaxRoute extends Route
{
	public function __construct(string $action, $callable, array $options = [])
	{
		$action = 'wp_ajax_' . preg_replace('/^wp_ajax_|^wp_ajax_nopriv_/', '', $action);

		$options['nopriv'] ??= false;

		parent::__construct($action, $callable, $options);
	}
}