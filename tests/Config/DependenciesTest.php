<?php declare(strict_types=1);

namespace JayWolfeLib\Tests\Config;

use JayWolfeLib\Config\Dependencies;
use WP_Mock;

class DependenciesTest extends \WP_Mock\Tools\TestCase
{
	public function setUp(): void
	{
		global $wp_version;
		$wp_version = '5.4';

		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
	}

	/**
	 * @covers \JayWolfeLib\Config\Dependencies::all
	 * @group dependencies
	 * @group config
	 */
	public function testCanGetAllDependencies()
	{
		$arr = $this->getDependenciesArray();

		$dependencies = new Dependencies($arr);
		$this->assertEquals($dependencies->all(), $arr);
	}

	/**
	 * @covers \JayWolfeLib\Config\Dependencies::set
	 * @group dependencies
	 * @group config
	 */
	public function testCanSetDependency()
	{
		$dependencies = new Dependencies([]);

		$dependencies->set('test', 123);
		$this->assertArrayHasKey('test', $dependencies->all());
		$this->assertEquals($dependencies->get('test'), 123);
	}

	/**
	 * @covers \JayWolfeLib\Config\Dependencies::get
	 * @group dependencies
	 * @group config
	 */
	public function testCanGetDependency()
	{
		$arr = $this->getDependenciesArray();

		$dependencies = new Dependencies($arr);
		$this->assertEquals('7.4', $dependencies->get('min_php_version'));
		$this->assertEquals('5.4', $dependencies->get('min_wp_version'));
	}

	/**
	 * @covers \JayWolfeLib\Config\Dependencies::has
	 * @group dependencies
	 * @group config
	 */
	public function testHasKey()
	{
		$arr = $this->getDependenciesArray();

		$dependencies = new Dependencies($arr);
		$this->assertTrue($dependencies->has('min_php_version'));
		$this->assertTrue($dependencies->has('min_wp_version'));

		$this->assertFalse($dependencies->has('test123'));
		$dependencies->set('test123', 123);
		$this->assertTrue($dependencies->has('test123'));
	}

	/**
	 * @covers \JayWolfeLib\Config\Dependencies::remove
	 * @group dependencies
	 * @group config
	 */
	public function testCanRemoveDependency()
	{
		$arr = $this->getDependenciesArray();

		$dependencies = new Dependencies($arr);
		$this->assertArrayHasKey('min_php_version', $dependencies->all());
		$dependencies->remove('min_php_version');
		$this->assertFalse(array_key_exists('min_php_version', $dependencies->all()));
	}

	/**
	 * @covers \JayWolfeLib\Config\Dependencies::remove
	 * @group dependencies
	 * @group config
	 */
	public function testCanClearDependencies()
	{
		$arr = $this->getDependenciesArray();

		$dependencies = new Dependencies($arr);
		$this->assertNotEmpty($dependencies->all());
		$dependencies->clear();
		$this->assertEmpty($dependencies->all());
	}

	/**
	 * @group dependencies
	 * @group config
	 * @group requirements_met
	 */
	public function testPHPVersionDependencyMet()
	{
		$dependencies = new Dependencies([
			'min_php_version' => '7.4'
		]);

		$this->assertTrue($dependencies->requirementsMet());
	}

	/**
	 * @group dependencies
	 * @group config
	 * @group requirements_met
	 */
	public function testPHPVersionDependencyNotMet()
	{
		$dependencies = new Dependencies([
			'min_php_version' => '999'
		]);

		$this->assertFalse($dependencies->requirementsMet());

		$errors = $dependencies->getErrors();
		$this->assertEquals(
			"PHP 999 is required.",
			$errors[0]->errorMessage
		);

		$this->assertEquals(
			sprintf("You're running version %s", PHP_VERSION),
			$errors[0]->info
		);
	}

	/**
	 * @group dependencies
	 * @group config
	 * @group requirements_met
	 */
	public function testWPVersionDependencyMet()
	{
		$dependencies = new Dependencies([
			'min_wp_version' => '5.4'
		]);

		$this->assertTrue($dependencies->requirementsMet());
	}

	/**
	 * @group dependencies
	 * @group config
	 * @group requirements_met
	 */
	public function testWPVersionDependencyNotMet()
	{
		global $wp_version;

		$dependencies = new Dependencies([
			'min_wp_version' => '999'
		]);

		$this->assertFalse($dependencies->requirementsMet());

		$errors = $dependencies->getErrors();
		$this->assertEquals(
			"WordPress 999 is required.",
			$errors[0]->errorMessage
		);

		$this->assertEquals(
			sprintf("You're running version %s", $wp_version),
			$errors[0]->info
		);
	}

	/**
	 * @group dependencies
	 * @group config
	 * @group requirements_met
	 */
	public function testPluginDependencyMet()
	{
		$arr = [
			'plugins' => [
				'mock/plugin.php'
			]
		];

		$dependencies = new Dependencies($arr);

		WP_Mock::userFunction('is_plugin_active', [
			'args' => 'mock/plugin.php',
			'return' => true
		]);

		$this->assertTrue($dependencies->requirementsMet());
	}

	/**
	 * @group dependencies
	 * @group config
	 * @group requirements_met
	 */
	public function testPluginDependencyNotMet()
	{
		$arr = [
			'plugins' => [
				'mock/plugin.php'
			]
		];

		$dependencies = new Dependencies($arr);

		WP_Mock::userFunction('is_plugin_active', [
			'args' => 'mock/plugin.php',
			'return' => false
		]);

		$this->assertFalse($dependencies->requirementsMet());

		$errors = $dependencies->getErrors();

		$this->assertEquals(
			'mock/plugin.php is a required plugin.',
			$errors[0]->errorMessage
		);

		$this->assertEquals(
			'mock/plugin.php needs to be installed and activated.',
			$errors[0]->info
		);
	}

	private function getDependenciesArray(): array
	{
		return [
			'min_php_version' => '7.4',
			'min_wp_version' => '5.4'
		];
	}
}