<?php

namespace JayWolfeLib\Tests\Models;

use JayWolfeLib\Container;
use JayWolfeLib\Models\Factory;
use JayWolfeLib\Models\ModelInterface;
use JayWolfeLib\Exception\InvalidModel;
use WP_Mock;
use Mockery;

class FactoryTest extends WP_Mock\Tools\TestCase
{
	private $mainContainer;

	public function setUp(): void
	{
		WP_Mock::setUp();

		global $wpdb;
		$wpdb = Mockery::mock('\WPDB');
		$wpdb->prefix = 'wp_';
		$this->mainContainer = new Container();
		Container::bootstrap($this->mainContainer);
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	public function testCanReturnModel(): void
	{
		$factory = new Factory(new Container(), $this->mainContainer);
		$mock = $factory->get(MockClass::class);

		$this->assertInstanceOf(MockClass::class, $mock);
		$this->assertInstanceOf(ModelInterface::class, $mock);
		$this->assertSame($mock, $factory->get(MockClass::class));
	}

	public function testCanCreateExternalModel(): void
	{
		$factory = new Factory(new Container(), $this->mainContainer);

		$mock = Mockery::mock('\Mock');

		$instance = $factory->get(get_class($mock));
		$this->assertInstanceOf(get_class($mock), $instance);
		$this->assertInstanceOf(ModelInterface::class, $instance);
	}

	public function testCanGetContainer(): void
	{
		$modelContainer = new Container();
		$factory = new Factory($modelContainer, $this->mainContainer);

		$container = $factory->get_container();

		$this->assertSame($container, $modelContainer);
	}

	public function testThrowsInvalidModel(): void
	{
		$factory = new Factory(new Container(), $this->mainContainer);

		$this->expectException(InvalidModel::class);

		$test = $factory->get('\Test');
	}
}