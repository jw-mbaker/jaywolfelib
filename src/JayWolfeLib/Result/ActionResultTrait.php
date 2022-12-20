<?php

namespace JayWolfeLib\Result;

use Symfony\Component\HttpFoundation\Request;

trait ActionResultTrait
{
	use ResultTrait;

	public function action(): string
	{
		return is_string($this->data['action']) ? $this->data['action'] : '';
	}

	public function request(): ?Request
	{
		return $this->data['request'] instanceof Request ? $this->data['request'] : null;
	}
}