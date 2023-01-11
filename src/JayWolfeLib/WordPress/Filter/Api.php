<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\Filter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Invoker\InvokerInterface;

class Api extends AbstractHook
{
	public const METHOD = 'method';
	public const REQUEST = 'request';
	public const API_KEY = 'api_key';

	public const DEFAULTS = [
		self::METHOD => 'GET',
		self::REQUEST => null,
		self::API_KEY => null
	];

	public const ACTION_NOT_RECOGNIZED = ['error' => 'Action not recognized.'];
	public const INVALID_METHOD = ['error' => 'Invalid server request method.'];

	private Request $request;
	private string $method;
	private ?string $apiKey;

	public function __construct(
		string $hook,
		$callable,
		string $method = self::DEFAULTS[self::METHOD],
		Request $request = self::DEFAULTS[self::REQUEST],
		?string $apiKey = self::DEFAULTS[self::API_KEY],
		array $map = parent::DEFAULTS[self::MAP]
	) {
		parent::__construct($hook, $callable, parent::DEFAULTS[self::PRIORITY], parent::DEFAULTS[self::NUM_ARGS], $map);

		$this->method = $method;
		$this->apiKey = $apiKey;

		$this->request = $request ??= Request::createFromGlobals();
	}

	public function method(): string
	{
		return $this->method;
	}

	public function apiKey(): string
	{
		return $this->apiKey;
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

		if (null !== $this->request->get('key') && $this->request->get('key') !== $this->apiKey) {
			return new JsonResponse(self::ACTION_NOT_RECOGNIZED, Response::HTTP_NOT_FOUND);
		}

		if ($this->request->getMethod() !== $this->method) {
			return new JsonResponse(self::INVALID_METHOD, Response::HTTP_NOT_FOUND);
		}
		
		return parent::__invoke($invoker, ...$arguments);
	}

	public static function create(array $args): self
	{
		$args = array_merge(parent::DEFAULTS, self::DEFAULTS, $args);

		return new self(
			$args[self::HOOK],
			$args[self::CALLABLE],
			$args[self::METHOD],
			$args[self::REQUEST],
			$args[self::API_KEY],
			$args[self::MAP]
		);
	}
}