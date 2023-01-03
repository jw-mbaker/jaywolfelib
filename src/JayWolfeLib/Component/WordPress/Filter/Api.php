<?php

namespace JayWolfeLib\Component\WordPress\Filter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Invoker\InvokerInterface;

class Api extends Action
{
	public const ACTION_NOT_RECOGNIZED = ['error' => 'Action not recognized.'];
	public const INVALID_METHOD = ['error' => 'Invalid server request method.'];

	private $request;
	private $method = 'GET';

	public function __construct(
		string $hook,
		$callable,
		string $method = 'GET',
		Request $request = null,
		array $settings = []
	) {
		$this->method = $method;

		$settings['api_key'] ??= null;

		$this->request = $request ??= Request::createFromGlobals();

		parent::__construct($hook, $callable, $settings);
	}

	public function method(): string
	{
		return $this->method;
	}

	public function __invoke(InvokerInterface $invoker, ...$arguments)
	{
		$headers = [];

		if ($this->request->getMethod() == 'POST') {
			$headers[] = "Access-Control-Allow-Origin: *";
		}

		$headers[] = "Content-Type:application/json";

		if (!headers_sent()) {
			foreach ($headers as $header) {
				header($header);
			}
		}

		if (null !== $this->request->get('key') && $this->request->get('key') !== $this->settings['api_key']) {
			return new JsonResponse(self::ACTION_NOT_RECOGNIZED, Response::HTTP_NOT_FOUND);
		}

		if ($this->request->getMethod() !== $this->method) {
			return new JsonResponse(self::INVALID_METHOD, Response::HTTP_NOT_FOUND);
		}
		
		return parent::__invoke($invoker, ...$arguments);
	}
}