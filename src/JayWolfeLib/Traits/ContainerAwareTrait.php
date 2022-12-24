<?php

namespace JayWolfeLib\Traits;

use Psr\Container\ContainerInterface;

trait ContainerAwareTrait
{
	/** @var ContainerInterface */
	protected $container;

	public function set_container(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function get_container(): ContainerInterface
	{
		return $this->container;
	}
}