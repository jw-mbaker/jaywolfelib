<?php

namespace JayWolfeLib\Result;

class ApiResult implements ResultInterface
{
	use ActionResultTrait;

	public function __construct(array $data = [])
	{
		$defaults = [
			'route' => null,
			'path' => null,
			'vars' => null,
			'matches' => null,
			'dependencies' => null,
			'api_key' => null
		];

		$this->data = array_merge($defaults, array_change_key_case($data, CASE_LOWER));
	}

	public function api_key(): string
	{
		return is_string($this->data['api_key']) ? $this->data['api_key'] : '';
	}
}