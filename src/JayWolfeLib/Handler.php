<?php

namespace JayWolfeLib;

use JayWolfeLib\Factory\ModelFactoryInterface;
use JayWolfeLib\Models\ModelInterface;

class Handler
{
	private $callback;
	private $container;
	private $dependencies;

	public function __construct(callable $callback, Container $container, array $dependencies = [])
	{
		$this->callback = $callback;
		$this->container = $container;
		$this->dependencies = $dependencies;
	}

	public function __invoke()
	{
		foreach ($this->dependencies as $k => $dependency) {
			if (is_string($dependency) && !\is_callable($dependency)) {
				$dependency = $this->container->get($dependency);
			} elseif (is_callable($dependency)) {
				$dependency = \call_user_func($dependency);
			}

			$this->dependencies[$k] = $dependency;
		}

		$this->container->call($this->callback, $this->dependencies);
	}

	public function add($dependency): self
	{
		$this->dependencies[] = $dependency;
		return $this;
	}
}