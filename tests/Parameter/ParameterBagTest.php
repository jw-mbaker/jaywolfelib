<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Parameter;

use JayWolfeLib\Parameter\ParameterBag;
use BadMethodCallException;

class ParameterBagTest extends \WP_Mock\Tools\TestCase
{
	public function testConstructor()
	{
		$this->testAll();
	}

	public function testAll()
	{
		$bag = $this->createParameterBag();
		$this->assertSame(['foo' => 'bar'], $bag->all());
	}

	public function testAllWithKey()
	{
		$bag = new ParameterBag(['foo' => ['bar', 'baz'], 'null' => null]);

		$this->assertEquals(['bar', 'baz'], $bag->all('foo'));
		$this->assertEquals([], $bag->all('unknown'));
	}

	public function testAllThrowsForNonArrayValues()
	{
		$this->expectException(BadMethodCallException::class);
		$bag = new ParameterBag(['foo' => 'bar', 'null' => null]);
		$bag->all('foo');
	}

	public function testKeys()
	{
		$bag = $this->createParameterBag();
		$this->assertSame(['foo'], $bag->keys());
	}

	public function testAdd()
	{
		$bag = $this->createParameterBag();
		$bag->add(['bar' => 'bas']);
		$this->assertEquals(['foo' => 'bar', 'bar' => 'bas'], $bag->all());
	}

	public function testReplace()
	{
		$bag = $this->createParameterBag();

		$bag->replace(['FOO' => 'BAR']);
		$this->assertEquals(['FOO' => 'BAR'], $bag->all());
		$this->assertFalse($bag->has('foo'));
	}

	public function testGet()
	{
		$bag = new ParameterBag(['foo' => 'bar', 'null' => null]);

		$this->assertEquals('bar', $bag->get('foo'));
		$this->assertEquals('default', $bag->get('unknown', 'default'));
		$this->assertNull($bag->get('null', 'default'));
	}

	public function testSet()
	{
		$bag = new ParameterBag();

		$bag->set('foo', 'bar');
		$this->assertEquals('bar', $bag->get('foo'));

		$bag->set('foo', 'baz');
		$this->assertEquals('baz', $bag->get('foo'));
	}

	public function testHas()
	{
		$bag = $this->createParameterBag();

		$this->assertTrue($bag->has('foo'));
		$this->assertFalse($bag->has('unknown'));
	}

	/**
	 * @dataProvider getParameters
	 */
	public function testGetIterator(array $parameters)
	{
		$bag = new ParameterBag($parameters);

		$i = 0;
		foreach ($bag as $key => $val) {
			$i++;
			$this->assertEquals($parameters[$key], $val);
		}

		$this->assertEquals(\count($parameters), $i);
	}

	/**
	 * @dataProvider getParameters
	 */
	public function testCount(array $parameters)
	{
		$bag = new ParameterBag($parameters);

		$this->assertCount(\count($parameters), $bag);
	}

	public function getParameters(): array
	{
		return [
			[
				[
					'foo' => 'bar',
					'hello' => 'world'
				]
			]
		];
	}

	private function createParameterBag(): ParameterBag
	{
		return new ParameterBag(['foo' => 'bar']);
	}
}