<?php

namespace JayWolfeLib\Guzzle;

use JayWolfeLib\Factory\GuzzleFactoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Uri;

class Factory implements GuzzleFactoryInterface
{
	/**
	 * Create a new instance based on the given key.
	 * 
	 * @param string $key
	 * @param array $opts
	 * @return mixed
	 */
	public function get(string $key, array $opts = [])
	{
		$key = sanitize_key($key);

		switch ($key) {
			case 'client':
				return $this->create_client($opts);
			case 'request':
				return $this->create_request($opts['method'], $opts['uri'], $opts['headers'] ?? [], $opts['body'] ?? null);
			case 'pool':
				return $this->create_pool($opts['client'], $opts['requests'], $opts['config'] ?? []);
			case 'stream':
				return $this->create_stream($opts['stream'], $opts['options'] ?? []);
			case 'uri':
				return $this->create_uri($opts['uri'] ?? '');
		}
	}

	/**
	 * Create a new client.
	 *
	 * @param array $config
	 * @return \GuzzleHttp\ClientInterface
	 */
	public function create_client(array $config = []): \GuzzleHttp\ClientInterface
	{
		$handlerStack = \GuzzleHttp\HandlerStack::create($config['handler'] ?? null);
		$config = array_merge($config, [
			'base_uri' => trailingslashit($config['base_uri'] ?? ''),
			'handler' => $handlerStack
		]);

		return new Client($config);
	}

	/**
	 * Create a new request.
	 *
	 * @param string $method
	 * @param string|\Psr\Http\Message\UriInterface $uri
	 * @param array $headers
	 * @param string|\Psr\Http\Message\StreamInterface $body
	 * @return \Psr\Http\Message\RequestInterface
	 */
	public function create_request(string $method, $uri, array $headers = [], $body = null): \Psr\Http\Message\RequestInterface
	{
		return new Request($method, $uri, $headers, $body);
	}

	/**
	 * Create a new pool.
	 *
	 * @param \GuzzleHttp\ClientInterface $client
	 * @param array|\Iterator $requests
	 * @param array $config
	 * @return \GuzzleHttp\Promise\PromisorInterface
	 */
	public function create_pool(\GuzzleHttp\ClientInterface $client, $requests, array $config = []): \GuzzleHttp\Promise\PromisorInterface
	{
		return new Pool($client, $requests, $config);
	}

	/**
	 * Create a new stream.
	 *
	 * @param resource $stream
	 * @param array $options
	 * @return \Psr\Http\Message\StreamInterface
	 */
	public function create_stream($stream, array $options = []): \Psr\Http\Message\StreamInterface
	{
		return new Stream($stream, $options);
	}

	/**
	 * Create a new uri.
	 *
	 * @param string $uri
	 * @return \Psr\Http\Message\UriInterface
	 */
	public function create_uri(string $uri = ''): \Psr\Http\Message\UriInterface
	{
		return new Uri($uri);
	}
}