<?php

namespace JayWolfeLib\Tests;

use JayWolfeLib\Input;
use WP_Mock;

class InputTest extends WP_Mock\Tools\TestCase
{
	private $input;

	public function setUp(): void
	{
		WP_Mock::setUp();
		WP_Mock::passthruFunction('esc_sql');
		WP_Mock::passthruFunction('esc_js');
		$this->input = new Input();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
	}

	public function testCanReadGetArray(): void
	{
		$_GET = [
			'test' => 1,
			'forminput' => [1, 2, 3]
		];

		$test = $this->input->get('test');

		$this->assertEquals($test, 1);

		$formInput = $this->input->get('forminput');
		$this->assertEquals($formInput, [1, 2, 3]);
	}

	public function testCanReadPostArray(): void
	{
		$_POST = [
			'test' => 1
		];

		$test = $this->input->post('test');

		$this->assertEquals($test, 1);
	}

	public function testCanReadRequestArray(): void
	{
		$_GET = [
			'testget' => 1
		];

		$_POST = [
			'testpost' => 2
		];

		$_COOKIE = [
			'testcookie' => 3
		];

		$testget = $this->input->request('testget');
		$this->assertEquals($testget, 1);

		$testpost = $this->input->request('testpost');
		$this->assertEquals($testpost, 2);


		$testcookie = $this->input->request('testcookie');
		$this->assertEquals($testcookie, 3);
	}

	public function testCanReadGetPostArray(): void
	{
		$_GET = ['testget' => 1];
		$_POST = ['testpost' => 99];

		$testget = $this->input->getpost('testget');
		$this->assertEquals($testget, 1);

		$testpost = $this->input->getpost('testpost');
		$this->assertEquals($testpost, 99);
	}
}