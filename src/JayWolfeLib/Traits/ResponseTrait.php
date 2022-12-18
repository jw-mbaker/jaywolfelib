<?php

namespace JayWolfeLib\Traits;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

trait ResponseTrait
{
	public function create_response(?string $content = '', int $status = 200, array $headers = []): Response
	{
		return new Response($content, $status, $headers);
	}

	public function send_response(?string $content = '', int $status = 200, array $headers = []): Response
	{
		return $this->create_response($content, $status, $headers)->send();
	}

	public function create_json_response(
		$data = null,
		int $status = 200,
		array $headers = [],
		bool $json = false
	): JsonResponse {
		return new JsonResponse($data, $status, $headers, $json);
	}

	public function send_json(
		$data = null,
		int $status = 200,
		array $headers = [],
		bool $json = false
	): JsonResponse {
		return $this->create_json_response($data, $status, $headers, $json)->send();
	}
}