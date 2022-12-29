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
		$callable = CallableReflection::create($handler->get('callable'));
		$params = $callable->getParameters();
		$resolved_args = [];

		foreach ($params as $param) {
			error_log('PARAM: ' . (string) $param . PHP_EOL);
			error_log('NAME: ' . $param->getName() . PHP_EOL);

			foreach ($arguments as $key => $arg) {
				$type = gettype($arg);
				if ($type === 'boolean') $type = 'bool';
				error_log('ARG TYPE: ' . $type . PHP_EOL);
				error_log('HAS TYPE: ' . var_export($param->hasType(), true) . PHP_EOL);
				if (!$param->hasType() || $this->resolve_type($type) === $this->resolve_type($param->getType()->getName())) {
					$resolved_args[$param->getName()] = $arg;
					unset($arguments[$key]);
					break;
				}
			}
		}

		error_log('RESOLVED ARGS: ' . var_export($resolved_args, true) . PHP_EOL);

		return $this->invoker->call($handler, ['args' => $resolved_args]);
	}

	private function resolve_type(string $type): string
	{
		switch ($type) {
			case 'int':
			case 'integer':
				return 'int';
			case 'float':
			case 'double':
				return 'float';
			case 'bool':
			case 'boolean':
				return 'bool';
			default:
				return $type;
		}
	}
}