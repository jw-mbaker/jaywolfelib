<?php declare(strict_types=1);

namespace JayWolfeLib\Parameter;

class ParameterBag implements ParameterInterface
{
	protected array $parameters;

	public function __construct(array $parameters = [])
	{
		$this->parameters = $parameters;
	}

	public function all(): array
	{
		$key = func_num_args() > 0 ? func_get_arg(0) : null;

		if (null === $key) {
			return $this->parameters;
		}

		if (!is_array($value = $this->parameters[$key] ?? [])) {
			throw new \BadMethodCallException(
				sprintf('Unexpected value parameter "%s": expecting "array", got "%s".', $key, gettype($key))
			);
		}

		return $value;
	}

	public function keys(): array
	{
		return array_keys($this->parameters);
	}

	/**
	 * Replaces the current parameters with a new set.
	 */
	public function replace(array $parameters = [])
	{
		$this->parameters = $parameters;
	}

	public function add(array $parameters = [])
	{
		$this->parameters = array_replace($this->parameters, $parameters);
	}

	/**
	 * Returns a parameter by name.
	 *
	 * @param mixed $default
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
	}

	/**
	 * Sets a parameter by name.
	 *
	 * @param mixed $value
	 */
	public function set(string $key, $value)
	{
		$this->parameters[$key] = $value;
	}

	public function has(string $key): bool
	{
		return array_key_exists($key, $this->parameters);
	}

	public function remove(string $key)
	{
		unset($this->parameters[$key]);
	}

	public function clear()
	{
		$this->parameters = [];
	}

	/**
	 * @return \ArrayIterator<string, mixed>
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->parameters);
	}

	public function count(): int
	{
		return \count($this->parameters);
	}
}