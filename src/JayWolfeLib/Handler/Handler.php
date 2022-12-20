<?php

namespace JayWolfeLib\Handler;

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

		return $this->container->call($this->callback, $this->dependencies);
	}

	public function add($dependency): self
	{
		$this->dependencies[] = $dependency;
		return $this;
	}

	public static function create($handler, array $dependencies = []): ?self
	{
		$container = apply_filters('jwlib_get_container', null);

		if (is_array($handler)) {
			if ($container->has($handler[0])) {
				$handler = [$container->get($handler[0]), $handler[1]];
			} else {
				throw new \Exception((string) $handler . ' not found in container.');
			}

			return new static($handler, $container, $dependencies);
		}

		return null;
	}
}