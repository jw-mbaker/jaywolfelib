<?php

namespace JayWolfeLib\Component\Routing;

use Symfony\Component\HttpFoundation\Response;

class Route implements RouteInterface
{
	use RouteTrait;

	public function __construct(string $action, $callable, array $options = [])
	{
		$this->action = $action;
		$this->callable = $callable;

		$options['type'] ??= 'action';
		$options['priority'] ??= 10;
		$options['num_args'] ??= 1;

		$this->options = $options;

		$this->id = 'route_' . spl_object_hash($this);
	}

	public function __invoke(InvokerInterface $invoker, array $arguments)
	{
		$response = $invoker->call($this->callable, $arguments);

		if ($response instanceof Response) {
			$response->send();
		}
	}
}