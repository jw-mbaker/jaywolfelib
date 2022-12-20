<?php

namespace JayWolfeLib\Handler;

use Symfony\Component\HttpFoundation\Response;

class ApiHandler
{
	use HandlerTrait;

	public function handle(ApiResult $result)
	{
		if ($result->matched()) {
			$orig_handler = $result->handler();
			$handler = $this->build_handler($orig_handler, $result->dependencies());

			$response = call_user_func($handler);

			if ($response instanceof Response) {
				$response->send();
			}

			wp_die();
		}
	}
}