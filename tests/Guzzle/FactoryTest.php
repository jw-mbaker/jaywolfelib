<?php

namespace JayWolfeLib\Tests\Guzzle;

use JayWolfeLib\Guzzle\Factory;
use JayWolfeLib\Container;
use WP_Mock;

class FactoryTest extends WP_Mock\Tools\TestCase
{
	private $factory;

	public function setUp(): void
	{
		WP_Mock::setUp();
		WP_Mock::passthruFunction('sanitize_key');
		$this->factory = new Factory();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
	}

	public function testCanGetClientInstanceFromGetMethod(): void
	{
		$client = $this->factory->get('client');
		$this->assertInstanceOf(\GuzzleHttp\ClientInterface::class, $client);
	}

	public function testCanGetRequestInstanceFromGetMethod(): void
	{
		$request = $this->factory->get('request', ['method' => 'GET', 'uri' => 'https://test.com']);
		$this->assertInstanceOf(\Psr\Http\Message\RequestInterface::class, $request);
	}

	public function testCanGetPoolInstanceFromGetMethod(): void
	{
		$pool = $this->factory->get('pool', ['client' => new \GuzzleHttp\Client(), 'requests' => [new \GuzzleHttp\Psr7\Request('GET', 'http://example.com')]]);
		$this->assertInstanceOf(\GuzzleHttp\Pool::class, $pool);
	}

	public function testCanGetStreamInstanceFromGetMethod(): void
	{
		$handle = fopen('php://temp', 'r');
		$stream = $this->factory->get('stream', ['stream' => $handle]);

		$this->assertInstanceOf(\Psr\Http\Message\StreamInterface::class, $stream);
		$stream->close();
	}

	public function testCanGetUriInstanceFromGetMethod(): void
	{
		$uri = $this->factory->get('uri', ['uri' => 'http://example.com']);
		$this->assertInstanceOf(\Psr\Http\Message\UriInterface::class, $uri);
	}
}