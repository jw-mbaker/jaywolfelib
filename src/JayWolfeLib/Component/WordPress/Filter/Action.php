<?php

namespace JayWolfeLib\Component\WordPress\Filter;

use Symfony\Component\HttpFoundation\Response;
use Invoker\InvokerInterface;

class Action extends Filter
{
	public const HOOK_TYPE = 'action';

	public function __invoke(InvokerInterface $invoker, ...$arguments)
	{
		$response = parent::__invoke($invoker, ...$arguments);

		if ($response instanceof Response) {
			$response->send();
		}

		if (defined('DOING_AJAX') && DOING_AJAX) {
			wp_die();
		}
	}
}
