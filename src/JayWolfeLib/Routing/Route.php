<?php

namespace JayWolfeLib\Routing;

class Route implements RouteInterface
{
	use RouteTrait;

	private const DEFAULTS = [
		'vars' => null,
		'default_vars' => null,
		'host' => null,
		'method' => null,
		'scheme' => null,
		'template' => null,
		'no_template' => null,
		'path' => null,
		'handler' => null,
		'dependencies' => null
	];

	public function __construct(array $data)
	{
		array_change_key_case($data, CASE_LOWER);
		$id = ! empty($data['id']) && is_string($data['id'])
			? $data['id']
			: 'route_' . spl_object_hash($this);

		$data = array_merge(self::DEFAULTS, $data);

		$this->add($data);
		$this->set('id', $id);
	}

	public function id(): string
	{
		return $this->settings['id'];
	}
}