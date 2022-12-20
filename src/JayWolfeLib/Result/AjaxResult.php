<?php

namespace JayWolfeLib\Result;

class AjaxResult implements ResultInterface
{
	public function __construct(array $data = [])
	{
		$this->data = $data;
	}
}