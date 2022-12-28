<?php

namespace JayWolfeLib\Tests\Traits;

use JayWolfeLib\Traits\SettingsTrait;
use WP_Mock;
use Mockery;

class SettingsTraitTest extends \WP_Mock\Tools\TestCase
{
	use SettingsTrait;

	/**
	 * @covers \JayWolfeLib\Traits\SettingsTrait::add
	 * @group SettingsTrait
	 */
	public function testCanAddArrayOfSettings()
	{
		$this->add([
			'test' => 123,
			'test2' => 456
		]);

		$this->assertArrayHasKey('test', $this->settings);
		$this->assertArrayHasKey('test2', $this->settings);
		$this->assertEquals($this->settings['test'], 123);
		$this->assertEquals($this->settings['test2'], 456);
	}

	/**
	 * @covers \JayWolfeLib\Traits\SettingsTrait::set
	 * @group SettingsTrait
	 */
	public function testCanSetValue()
	{
		$this->set('test', 123);

		$this->assertArrayHasKey('test', $this->settings);
		$this->assertEquals($this->settings['test'], 123);
	}

	/**
	 * @covers \JayWolfeLib\Traits\SettingsTrait::get
	 * @group SettingsTrait
	 */
	public function testCanGetValue()
	{
		$this->settings['test'] = 'test123';
		$this->assertEquals($this->get('test'), $this->settings['test']);
	}

	/**
	 * @covers \JayWolfeLib\Traits\SettingsTrait::get
	 * @group SettingsTrait
	 */
	public function testNonExistentKeyReturnsNull()
	{
		$this->assertNull($this->get('test'));
	}

	/**
	 * @covers \JayWolfeLib\Traits\SettingsTrait::remove
	 * @group SettingsTrait
	 * @depends testCanSetValue
	 */
	public function testCanRemoveValue()
	{
		$this->set('test', 123);
		$this->assertArrayHasKey('test', $this->settings);

		$this->remove('test');
		$this->assertFalse(isset($this->settings['test']));
	}

	/**
	 * @covers \JayWolfeLib\Traits\SettingsTrait::all
	 * @group SettingsTrait
	 */
	public function testCanGetAllSettings()
	{
		$this->populateSettings();

		$this->assertEquals($this->all(), $this->settings);
	}

	/**
	 * @covers \JayWolfeLib\Traits\SettingsTrait::has
	 * @group SettingsTrait
	 */
	public function testHasKey()
	{
		$this->populateSettings();

		$this->assertTrue($this->has('test'));
		$this->assertTrue($this->has('test2'));
		$this->assertTrue($this->has('test3'));
		$this->assertFalse($this->has('xyz'));
		$this->assertFalse($this->has('sdfds'));
	}

	/**
	 * @covers \JayWolfeLib\Traits\SettingsTrait::clear
	 * @group SettingsTrait
	 */
	public function testClearSettings()
	{
		$this->populateSettings();

		$this->assertNotEmpty($this->settings);
		$this->clear();
		$this->assertEmpty($this->settings);
	}

	private function populateSettings()
	{
		$this->settings = [
			'test' => 123,
			'test2' => 456,
			'test3' => 789
		];
	}
}