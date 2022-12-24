<?php

namespace JayWolfeLib\Controllers;

use JayWolfeLib\Traits\ConfigTrait;
use JayWolfeLib\Traits\ContainerAwareTrait;
use Psr\Container\ContainerInterface;

/**
 * The controller base class.
 */
abstract class AbstractController implements ControllerInterface
{
	use ConfigTrait;
	use ContainerAwareTrait;

	public function __construct()
	{
		
	}

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
}