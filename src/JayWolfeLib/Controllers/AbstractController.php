<?php declare(strict_types=1);

namespace JayWolfeLib\Controllers;

use JayWolfeLib\Views\ViewFactory;
use JayWolfeLib\Config\ConfigInterface;
use JayWolfeLib\Contracts\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Psr\Container\ContainerInterface;

/**
 * The controller base class.
 */
abstract class AbstractController implements ControllerInterface, ContainerAwareInterface
{
	private ContainerInterface $container;
	protected ConfigInterface $config;

	/**
	 * @Inject
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getContainer(): ContainerInterface
	{
		return $this->container;
	}

	protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
	{
		return new JsonResponse($data, $status, $headers);
	}

	/**
	 * @param array<string, string> $parameters
	 * @throws \BadMethodCallException
	 */
	protected function renderView(string $template, array $parameters = []): string
	{
		if (!$this->config instanceof ConfigInterface) {
			throw new \BadMethodCallException(
				sprintf('$config must be set to use %s.', __METHOD__)
			);
		}

		return $this->container->get(ViewFactory::class)->make($template, $parameters, null, $this->config);
	}

	protected function render(string $template, array $parameters = [], ?Response $response = null): Response
	{
		$content = $this->renderView($template, $parameters);
		$response ??= new Response($content);

		return $response;
	}

	/**
	 * Returns a BinaryFileResponse object with original or customized file name and disposition header.
	 *
	 * @param \SplFileInfo|string $file
	 * @return BinaryFileResponse
	 */
	protected function file($file, string $file_name = null, string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT): BinaryFileResponse
	{
		$response = new BinaryFileResponse($file);
		$response->setContentDisposition($disposition, null === $file_name ? $response->getFile()->getFilename() : $file_name);

		return $response;
	}
}