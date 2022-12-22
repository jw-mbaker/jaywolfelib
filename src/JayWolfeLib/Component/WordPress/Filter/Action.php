<?php

namespace JayWolfeLib\Component\WordPress\Filter;

use Symfony\Component\HttpFoundation\Response;

class Action extends Filter
{
	public function __construct(string $hook, $callable, array $settings = [])
	{
		parent::__construct($hook, $callable, $settings);

		$this->id = 'action_' . spl_object_hash($this);
	}

	public function __invoke(InvokerInterface $invoker, array $arguments)
	{
		$response = parent::__invoke($invoker, $arguments);

		if ($response instanceof Response) {
			$response->send();
		}

		if (defined('DOING_AJAX') && DOING_AJAX) {
			wp_die();
		}
	}
}