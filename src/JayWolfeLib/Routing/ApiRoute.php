<?php

namespace JayWolfeLib\Routing;

class ApiRoute extends Route
{
	const DEFAULTS = [
		'action' => null,
		'path' => '/api',
		'api_key' => null
	];

	public function __construct(string $action, array $options = [])
	{
		$options = array_merge(self::DEFAULTS, $options);
		parent::__construct($options);
		$this->set('action', $action);
	}
}