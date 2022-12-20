<?php

namespace JayWolfeLib\Result;

trait ResultTrait
{
	private $data = [];

	public function route(): string
	{
		return is_string($this->data['route']) ? $this->data['route'] : '';
	}

	public function matched_path(): string
	{
		return is_string($this->data['path']) ? $this->data['path'] : '';
	}

	public function vars(): array
	{
		return is_array($this->data['vars']) ? $this->data['vars'] : [];
	}

	public function matches(): array
	{
		return is_array($this->data['matches']) ? $this->data['matches'] : [];
	}

	public function matched(): bool
	{
		return $this->route() && $this->matched_path();
	}

	public function handler()
	{
		return $this->data['handler'] ?? null;
	}

	public function dependencies(): array
	{
		return is_array($this->data['dependencies']) ? $this->data['dependencies'] : [];
	}
}