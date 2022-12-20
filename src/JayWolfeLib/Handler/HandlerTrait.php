<?php

namespace JayWolfeLib\Handler;

trait HandlerTrait
{
	private function build_handler($handler, array $dependencies = [])
	{
		$res = Handler::create($handler, $dependencies);

		return $res ?? $handler;
	}
}