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
	 * @covers \JayWolfeLib\Component\Config\ConfigCollection::add
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

	/**
	 * @covers \JayWolfeLib\Component\Config\ConfigCollection::all
	 * @dataProvider configProvider
	 * @group config
	 * @group config_collection
	 * @group collection
	 */
	public function testCanRetrieveAllConfigs(array $configs)
	{
		foreach ($configs as $key => $config) {
			$this->collection->add($key, $config);
		}

		$all = $this->collection->all();

		$this->assertSame($configs['test1'], $all['test1']);
		$this->assertSame($configs['test2'], $all['test2']);
		$this->assertSame($configs['test3'], $all['test3']);

		$this->assertEquals($configs, $this->collection->all());
	}

	/**
	 * @covers \JayWolfeLib\Component\Config\ConfigCollection::get
	 * @dataProvider configProvider
	 * @group config
	 * @group config_collection
	 * @group collection
	 */
	public function testCanRetrieveConfig(array $configs)
	{
		foreach ($configs as $key => $config) {
			$this->collection->add($key, $config);
		}

		$this->assertSame($configs['test1'], $this->collection->get('test1'));
		$this->assertSame($configs['test2'], $this->collection->get('test2'));
		$this->assertSame($configs['test3'], $this->collection->get('test3'));
	}

	/**
	 * @covers \JayWolfeLib\Component\Config\ConfigCollection::remove
	 * @group config
	 * @group config_collection
	 * @group collection
	 */
	public function testCanRemoveSingleConfig()
	{
		$config = $this->createMockConfig();

		$this->collection->add('test', $config);

		$this->assertNotEmpty($this->collection->all());
		$this->assertArrayHasKey('test', $this->collection->all());
		$this->assertSame($config, $this->collection->get('test'));
		$this->collection->remove('test');

		$this->assertEmpty($this->collection->all());
		$this->assertNull($this->collection->get('test'));
	}

	/**
	 * @covers \JayWolfeLib\Component\Config\ConfigCollection::remove
	 * @dataProvider configProvider
	 * @group config
	 * @group config_collection
	 * @group collection
	 */
	public function testCanRemoveMultipleConfigs(array $configs)
	{
		foreach ($configs as $key => $config) {
			$this->collection->add($key, $config);
		}

		$all = $this->collection->all();

		$this->assertNotEmpty($all);
		$this->assertArrayHasKey('test1', $all);
		$this->assertArrayHasKey('test2', $all);
		$this->assertArrayHasKey('test3', $all);

		$this->collection->remove(['test1', 'test2', 'test3']);
		$this->assertEmpty($this->collection->all());
	}

	public function configProvider(): array
	{
		$configs = [
			'test1' => $this->createMockConfig(),
			'test2' => $this->createMockConfig(),
			'test3' => $this->createMockConfig()
		];

		return array(
			array(
				$configs
			)
		);
	}
}