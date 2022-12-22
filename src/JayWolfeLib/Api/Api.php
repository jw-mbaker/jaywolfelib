<?php

namespace JayWolfeLib\Api;

use JayWolfeLib\Hooks\Hooks;
use JayWolfeLib\Hooks\Handler;
use JayWolfeLib\Traits\RequestTrait;
use JayWolfeLib\Traits\ResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use function JayWolfeLib\container;

/**
 * @deprecated 3.0.0
 */
class Api
{
	use RequestTrait, ResponseTrait;

	/**
	 * The Api hooks.
	 *
	 * @var array
	 */
	protected $hooks = [];

	/**
	 * Return array when action is not recognized.
	 * 
	 * @var array
	 */
	public const ACTION_NOT_RECOGNIZED = ['error' => 'Action not recognized.'];

	/**
	 * Return array when request method is invalid.
	 * 
	 * @var array
	 */
	public const INVALID_METHOD = ['error' => 'Invalid server request method.'];

	/**
	 * Constructor.
	 */
	public function __construct(Request $request)
	{
		$this->set_request($request);
		Hooks::add_action('wp', [$this, 'do_api']);
	}

	/**
	 * Register an API hook.
	 *
	 * @param string $hook
	 * @param callable $callback
	 * @param string $method
	 * @param string|null $api_key
	 * @return self
	 */
	public function register_hook(
		string $hook,
		callable $callback,
		string $method = 'GET',
		?string $api_key = null
	): self {
		$this->hooks[$hook] = [
			'callback' => $callback,
			'method' => $method,
			'api_key' => $api_key
		];

		Hooks::add_action($hook, $callback);

		return $this;
	}

	/**
	 * Execute API hooks.
	 *
	 * @return void
	 */
	public function do_api(): void
	{
		$headers = [];
		$data = [];

		if ($this->request->getMethod() == 'POST') {
			$headers[] = "Access-Control-Allow-Origin: *";
		}

		if (null === $this->request->get('action'))
			return;
		
		$headers[] = "Content-Type:application/json";

		$hook = $this->request->get('action');

		if (!isset($this->hooks[$hook]))
			return;

		if (!headers_sent()) {
			foreach ($headers as $header) {
				header($header);
			}
		}

		if (null !== $this->request->get('key') && $this->request->get('key') !== $this->hooks[$hook]['api_key']) {
			$this->send_json(self::ACTION_NOT_RECOGNIZED, 404);
			return;
		}
			
		if ($this->request->getMethod() !== $this->hooks[$hook]['method']) {
			$this->send_json(self::INVALID_METHOD, 404);
			return;
		}
		
		Hooks::do_action($hook);
		wp_die();
	}

	/**
	 * Register an API hook.
	 *
	 * @param string $hook
	 * @param callable $callback
	 * @param string $method
	 * @param string|null $api_key
	 * @return Handler
	 */
	public static function add_api(
		string $hook,
		callable $callback,
		string $method = 'GET',
		?string $api_key = null
	): Handler {
		$handler = new Handler( container()->get('request'), $callback );

		self::get_api_manager()->register_hook($hook, $handler, $method, $api_key);

		return $handler;
	}

	/**
	 * Get the API instance.
	 *
	 * @return self
	 */
	public static function get_api_manager(): self
	{
		return container()->get('api');
	}
}