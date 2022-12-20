<?php

namespace JayWolfeLib\Hooks;

use JayWolfeLib\Container;
use JayWolfeLib\Factory\BaseFactoryInterface;
use JayWolfeLib\Traits\RequestTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handler class
 */
class Handler
{
	use RequestTrait;

	/**
	 * The callback that the handler will call when the action is invoked.
	 * 
	 * @var \Closure
	 */
	private $callback;

	/**
	 * An array of dependencies that will be injected into the callback function.
	 *
	 * @var array
	 */
	private $dependencies = [];

	public function __construct(Request $request, callable $callback)
	{
		$this->set_request($request);
		$this->callback = $callback;
	}

	/**
	 * Execute the callback function when the action is invoked.
	 *
	 * @return void
	 */
	public function __invoke()
	{
		foreach ($this->dependencies as $k => $dependency) {
			if (is_string($dependency) && class_exists($dependency)) {
				$dependency = new $dependency();
			} elseif (is_array($dependency) && ($dependency[0] instanceof Container || $dependency[0] instanceof BaseFactoryInterface)) {
				$dependency = $dependency[0]->get($dependency[1]);
			} elseif (is_callable($dependency)) {
				$dependency = call_user_func($dependency);
			}

			$this->dependencies[$k] = $dependency;
		}

		call_user_func($this->callback, $this->request, ...$this->dependencies);
	}

	/**
	 * Inject a depency into the callback function that will be
	 * called when the action is invoked.
	 *
	 * @param mixed $dependency Can be a generic value or a string representing a class
	 *                          or an array referencing a container object
	 *                          and its key. ex. [$container, 'convertapi']
	 * @return self
	 */
	public function with($dependency): self
	{
		$this->dependencies[] = $dependency;

		return $this;
	}
}