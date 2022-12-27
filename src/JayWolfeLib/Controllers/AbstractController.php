<?php

namespace JayWolfeLib\Controllers;

use JayWolfeLib\Views\View;
use JayWolfeLib\Traits\ConfigTrait;
use JayWolfeLib\Traits\ContainerAwareTrait;
use JayWolfeLib\Exception\InvalidConfig;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Container\ContainerInterface;

/**
 * The controller base class.
 */
abstract class AbstractController implements ControllerInterface
{
	use ConfigTrait;
	use ContainerAwareTrait;

	protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
	{
		return new JsonResponse($data, $status, $headers);
	}

	protected function render_view(string $template, array $parameters = []): string
	{
		return View::make($template, $parameters, null, $this->config);
	}

	protected function render(string $template, array $parameters = [], Response $response = null): Response
	{
		$content = $this->render_view($template, $parameters);
		$response ??= new Response($content);

		return $response;
	}
}