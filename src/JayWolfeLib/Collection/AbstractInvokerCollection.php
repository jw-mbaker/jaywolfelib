<?php declare(strict_types=1);

namespace JayWolfeLib\Collection;

use JayWolfeLib\Invoker\CallerInterface;
use JayWolfeLib\Invoker\HandlerInterface;
use Invoker\InvokerInterface;

abstract class AbstractInvokerCollection extends AbstractCollection implements CallerInterface
{
	protected InvokerInterface $invoker;

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
		$map = $handler->map();
		$arguments = array_merge([$this->invoker], array_values($map), $arguments);
		return $this->invoker->call($handler, $arguments);
	}
}