<?php declare(strict_types=1);

namespace JayWolfeLib\Contracts;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface
{
	public function setContainer(ContainerInterface $container);
	public function getContainer(): ContainerInterface;
}