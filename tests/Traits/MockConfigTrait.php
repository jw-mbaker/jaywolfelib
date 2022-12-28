<?php

namespace JayWolfeLib\Tests\Traits;

use JayWolfeLib\Component\Config\ConfigInterface;
use Mockery;

trait MockConfigTrait
{
	private function createMockConfig(): ConfigInterface
	{
		return Mockery::mock(ConfigInterface::class);
	}
}