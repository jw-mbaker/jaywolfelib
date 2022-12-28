<?php

namespace JayWolfeLib\Tests\Component\Config;

use JayWolfeLib\Component\Config\ConfigCollection;
use JayWolfeLib\Component\Config\ConfigInterface;
use JayWolfeLib\Component\Config\Config;
use JayWolfeLib\Tests\Traits\MockConfigTrait;
use WP_Mock;
use Mockery;

class ConfigCollectionTest extends \WP_Mock\Tools\TestCase
{
	use MockConfigTrait;

	private $collection;

	public function setUp(): void
	{
		WP_Mock::setUp();
		$this->collection = new ConfigCollection();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group config
	 * @group config_collection
	 * @group collection
	 */
	public function testCanAddConfig()
	{
		$config = $this->createMockConfig();
		$this->collection->add('test', $config);

		$this->assertSame($config, $this->collection->get('test'));
	}
}