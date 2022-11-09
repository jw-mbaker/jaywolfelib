<?php

namespace JayWolfeLib\Tests;

use JayWolfeLib\Container;
use DownShift\WordPress\EventEmitterInterface;
use WP_Mock;
use Mockery;

class ContainerTest extends WP_Mock\Tools\TestCase
{
	public function setUp(): void
	{
		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	public function testCanAddToContainer(): void
	{
		$container = new Container();
		$mockClass = Mockery::mock('\MockClass');

		$container->set('mock', $mockClass);

		$this->assertSame($mockClass, $container->get('mock'));
	}

	public function testCanTriggerInit(): void
	{
		$container = new Container();

		$mock = new class() {
			public $val;

			public function init()
			{
				$this->val = 1;
			}
		};

		$container->init('mock', get_class($mock));

		$this->assertEquals($container->get('mock')->val, 1);
	}

	public function testCanEmptyContainer(): void
	{
		$container = new Container();

		$container->set('test', 1);
		$this->assertEquals($container->get('test'), 1);

		$container->flush();

		$this->expectException(\Pimple\Exception\UnknownIdentifierException::class);
		$container->get('test');
	}

	public function testCanBootstrapContainer(): void
	{
		global $wpdb;

		$wpdb = Mockery::mock('\WPDB');
		$container = new Container();

		Container::bootstrap($container);

		$this->assertInstanceOf(EventEmitterInterface::class, $container->get('hooks'));
		$this->assertSame($container->get('wpdb'), $wpdb);
		$this->assertInstanceOf(\JayWolfeLib\Models\Factory::class, $container->get('models'));
		$this->assertInstanceOf(\JayWolfeLib\Controllers\Factory::class, $container->get('controllers'));
	}
}