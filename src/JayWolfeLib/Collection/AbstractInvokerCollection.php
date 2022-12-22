<?php

namespace JayWolfeLib\Collection;

use JayWolfeLib\Component\CallerInterface;
use Invoker\InvokerInterface;

abstract class AbstractInvokerCollection extends AbstractCollection implements CallerInterface
{
	/** @var InvokerInterface */
	protected $invoker;

	public function __construct(InvokerInterface $invoker)
	{
		parent::__construct();
		$this->invoker = $invoker;
	}

	protected function get_invoker(): InvokerInterface
	{
		return $this->invoker;
	}
}