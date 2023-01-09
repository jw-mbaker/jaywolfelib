<?php declare(strict_types=1);

namespace JayWolfeLib\Traits;

use Psr\Container\ContainerInterface;

trait ContainerAwareTrait
{
	/** @var ContainerInterface */
	protected $container;

	/**
	 * Set the container.
	 *
	 * @Inject
	 * @param ContainerInterface $container
	 */
	public function set_container(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function get_container(): ContainerInterface
	{
		return $this->container;
	}
}