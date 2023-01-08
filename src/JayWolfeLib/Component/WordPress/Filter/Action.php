<?php declare(strict_types=1);

namespace JayWolfeLib\Component\WordPress\Filter;

use Symfony\Component\HttpFoundation\Response;
use Invoker\InvokerInterface;

class Action extends Filter
{
	public const HOOK_TYPE = 'action';
}
