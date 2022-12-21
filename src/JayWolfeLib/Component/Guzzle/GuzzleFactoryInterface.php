<?php

namespace JayWolfeLib\Factory;

use JayWolfeLib\Factory\BaseFactoryInterface;

interface GuzzleFactoryInterface extends BaseFactoryInterface
{
	public function get(string $key, array $opts = []);
	public function create_client(array $config = []): \GuzzleHttp\ClientInterface;
	public function create_request(string $method, $uri, array $headers = [], $body = null): \Psr\Http\Message\RequestInterface;
	public function create_pool(\GuzzleHttp\ClientInterface $client, $requests, array $config = []): \GuzzleHttp\Promise\PromisorInterface;
	public function create_stream($stream, array $options = []): \Psr\Http\Message\StreamInterface;
	public function create_uri(string $uri = ''): \Psr\Http\Message\UriInterface;
}