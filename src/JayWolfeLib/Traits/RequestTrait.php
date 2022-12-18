<?php

namespace JayWolfeLib\Traits;

use Symfony\Component\HttpFoundation\Request;

trait RequestTrait
{
	protected $request;

	public function set_request(Request $request): void
	{
		$this->request = $request;
	}

	public function get_request(): Request
	{
		return $this->request;
	}
}