<?php

namespace JayWolfeLib\Component\WordPress\Filter;

use JayWolfeLib\Traits\ResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Invoker\InvokerInterface;

class Api extends Action
{
	use ResponseTrait;

	public const ACTION_NOT_RECOGNIZED = ['error' => 'Action not recognized.'];
	public const INVALID_METHOD = ['error' => 'Invalid server request method.'];

	private $method = 'GET';

	public function __construct(string $hook, $callable, string $method = 'GET', array $settings = [])
	{
		$this->method = $method;
		$settings['api_key'] ??= null;

		parent::__construct($hook, $callable, $settings);
	}

	public function method(): string
	{
		return $this->method;
	}

	public function __invoke(Request $request, InvokerInterface $invoker, ...$arguments)
	{
		$headers = [];

		if ($request->getMethod() == 'POST') {
			$headers[] = "Access-Control-Allow-Origin: *";
		}

		$headers[] = "Content-Type:application/json";

		if (!headers_sent()) {
			foreach ($headers as $header) {
				header($header);
			}
		}

		if (null !== $request->get('key') && $request->get('key') !== $this->settings['api_key']) {
			$this->send_json(self::ACTION_NOT_RECOGNIZED, 404);
			return;
		}

		if ($request->getMethod() !== $this->method) {
			$this->send_json(self::INVALID_METHOD, 404);
			return;
		}
		
		parent::__invoke($invoker, ...$arguments);
		wp_die();
	}
}