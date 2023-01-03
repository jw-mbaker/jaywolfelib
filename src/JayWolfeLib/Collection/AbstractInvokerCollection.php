<?php

namespace JayWolfeLib\Collection;

use JayWolfeLib\Component\CallerInterface;
use JayWolfeLib\Component\HandlerInterface;
use Invoker\InvokerInterface;
use Invoker\Reflection\CallableReflection;
use ReflectionType;

abstract class AbstractInvokerCollection extends AbstractCollection implements CallerInterface
{
	/** @var InvokerInterface */
	protected $invoker;

	public function __construct(InvokerInterface $invoker)
	{
		$this->invoker = $invoker;
	}

	public function get_invoker(): InvokerInterface
	{
		return $this->invoker;
	}

	protected function resolve(HandlerInterface $handler, array $arguments)
	{
		$map = $handler->get('map');
		$arguments = array_merge([$this->invoker], array_values($map), $arguments);
		return $this->invoker->call($handler, $arguments);
	}
}