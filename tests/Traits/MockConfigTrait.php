<?php

namespace JayWolfeLib\Tests\Traits;

use JayWolfeLib\Config\ConfigInterface;
use Mockery;

trait MockConfigTrait
{
	private function createMockConfig(): ConfigInterface
	{
		return Mockery::mock(ConfigInterface::class);
	}
}