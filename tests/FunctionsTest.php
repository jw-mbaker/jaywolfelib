<?php

namespace JayWolfeLib\Tests;

use JayWolfeLib\Component\Config\ConfigInterface;
use JayWolfeLib\Tests\Traits\MockConfigTrait;
use WP_Mock;
use Mockery;

use function JayWolfeLib\install;
use function JayWolfeLib\update_db_check;
use function JayWolfeLib\validate_bool;
use function JayWolfeLib\rrmdir;
use function JayWolfeLib\fetch_array;
use function JayWolfeLib\fragment_cache;
use function JayWolfeLib\delete_fragment_cache;
use function JayWolfeLib\snake_case;

use const JayWolfeLib\MOCK_PLUGIN_REL_PATH;
use const JayWolfeLib\MOCK_ARRAY_PATH;

class FunctionsTest extends \WP_Mock\Tools\TestCase
{
	use MockConfigTrait;

	public function setUp(): void
	{
		global $wpdb;

		WP_Mock::setUp();
		WP_Mock::alias('trailingslashit', function($str) {
			return rtrim($str, '\\/') . DIRECTORY_SEPARATOR;
		});
		WP_Mock::alias('plugin_basename', function($file) {
			return basename($file);
		});

		$wpdb = Mockery::mock(\WPDB::class);
		$wpdb->prefix = 'wp';
	}

	public function tearDown(): void
	{
		if (file_exists(trailingslashit( ABSPATH ) . 'mock-config-no-db.php')) {
			unlink(trailingslashit( ABSPATH ) . 'mock-config-no-db.php');
		}

		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group fetch_array
	 * @group config
	 * @group install
	 * @doesNotPerformAssertions
	 */
	public function testInstallVersionsMatch()
	{
		global $wpdb;

		WP_Mock::passthruFunction('sanitize_key');
		WP_Mock::userFunction('add_option');
		WP_Mock::userFunction('get_option', [
			'args' => 'dummy_db_db_version',
			'return' => '1.0.0'
		]);

		$config = $this->createMockConfig();
		$dir = $this->fetchArrayDir();

		$config
			->expects()
			->get('db')
			->twice()
			->andReturn('dummy_db');

		$config
			->expects()
			->get('paths')
			->twice()
			->andReturn([
				'arrays' => $dir
			]);

		$wpdb->expects()->get_charset_collate()->andReturn('');

		install($config);
	}

	/**
	 * @group fetch_array
	 * @group config
	 * @group install
	 * @doesNotPerformAssertions
	 */
	public function testInstallVersionsNotMatchShouldCallCreateTable()
	{
		global $wpdb;

		WP_Mock::passthruFunction('sanitize_key');
		WP_Mock::userFunction('add_option');
		WP_Mock::userFunction('get_option', [
			'args' => 'dummy_db_db_version',
			'return' => '0.9.9'
		]);
		WP_Mock::userFunction('update_option', [
			'args' => ['dummy_db_db_version', '1.0.0']
		]);
		WP_Mock::userFunction('dbDelta', ['times' => 1]);

		$config = $this->createMockConfig();
		$dir = $this->fetchArrayDir();

		$config
			->expects()
			->get('db')
			->twice()
			->andReturn('dummy_db');

		$config
			->expects()
			->get('paths')
			->twice()
			->andReturn([
				'arrays' => $dir
			]);

		$wpdb->expects()->get_charset_collate()->andReturn('');

		install($config);
	}

	/**
	 * @group fetch_array
	 * @group config
	 * @group install
	 */
	public function testInstallThrowsInvalidConfigOnNonFile()
	{
		$file = 'xyz.file';
		$this->expectException(\JayWolfeLib\Exception\InvalidConfigException::class);
		$this->expectExceptionMessage(
			sprintf('%s not found.', $file)
		);

		install($file);
	}

	/**
	 * @group config
	 * @group install
	 */
	public function testInstallThrowsInvalidConfigOnNoDB()
	{
		$file = trailingslashit( ABSPATH ) . 'mock-config-no-db.php';

		file_put_contents($file, "<?php return [
			'plugin_file' => JayWolfeLib\\MOCK_PLUGIN_REL_PATH,
		];");

		$this->assertTrue(file_exists($file));

		$this->expectException(\JayWolfeLib\Exception\InvalidConfigException::class);
		$this->expectExceptionMessage(
			sprintf('"db" option not set for %s.', basename(MOCK_PLUGIN_REL_PATH))
		);

		install($file);
	}

	/**
	 * @group config
	 * @group install
	 * @group fetch_array
	 * @doesNotPerformAssertions
	 */
	public function testUpdateDBCheckVersionsMatch()
	{
		WP_Mock::passthruFunction('sanitize_key');
		WP_Mock::userFunction('get_option', [
			'args' => 'dummy_db_db_version',
			'return' => '1.0.0'
		]);

		$config = $this->createMockConfig();
		$dir = $this->fetchArrayDir();

		$config
			->expects()
			->get('db')
			->twice()
			->andReturn('dummy_db');

		$config
			->expects()
			->get('paths')
			->twice()
			->andReturn([
				'arrays' => $dir
			]);

		update_db_check($config);
	}

	public function testValidateBool()
	{
		$this->assertTrue( validate_bool(true) );
		$this->assertTrue( validate_bool('true') );
		$this->assertTrue( validate_bool(1) );
		$this->assertTrue( validate_bool('yes') );

		$this->assertFalse( validate_bool(false) );
		$this->assertFalse( validate_bool('false') );
		$this->assertFalse( validate_bool(0) );
		$this->assertFalse( validate_bool('no') );

		$this->assertNull( validate_bool('xyz') );
		$this->assertNull( validate_bool('www.website.com') );
	}

	/**
	 * @group rrmdir
	 */
	public function testRrmdir()
	{
		$dir = trailingslashit( ABSPATH ) . 'mock-dir';
		$file = trailingslashit($dir) . 'mock-file.php';

		if (is_dir($dir)) {
			rmdir($dir);
		}

		mkdir($dir, 0755, true);
		file_put_contents($file, '<?php //silence is golden');

		rrmdir($dir);

		$this->assertFalse(is_dir($dir));
		$this->assertFalse(file_exists($file));
	}

	/**
	 * @group rrmdir
	 */
	public function testRrmdirInvalidDir()
	{
		$dir = 'sdfsdfs';

		$this->assertFalse(is_dir($dir));

		rrmdir($dir);

		$this->assertFalse(is_dir($dir));
	}

	/**
	 * @group fragment_cache
	 */
	public function testFragmentCache()
	{
		$this->fragmentCacheSetup();
		$ret = fragment_cache('test', 999, function() {});

		$this->assertEquals($ret, 'test123');
	}

	/**
	 * @group fragment_cache
	 */
	public function testFragmentCacheClearFragments()
	{
		$this->fragmentCacheSetup();

		WP_Mock::userFunction('set_transient', [
			'args' => [ 'fragment_cache_test', 'test123', 999 ]
		]);

		$_GET = ['clear_fragments' => true];
		$ret = fragment_cache('test', 999, function($test) {
			return $test;
		}, ['test123']);

		$this->assertEquals($ret, 'test123');
	}

	/**
	 * @doesNotPerformAssertions
	 * @group fragment_cache
	 */
	public function testDeleteFragmentCache()
	{
		$this->fragmentCacheSetup();
		WP_Mock::userFunction('delete_transient', [
			'args' => 'fragment_cache_test'
		]);

		delete_fragment_cache('test');
	}

	/**
	 * @group fetch_array
	 */
	public function testFetchArray()
	{
		$dir = $this->fetchArrayDir();
		$file = trailingslashit($dir) . 'test.php';
		$this->assertTrue(is_readable($file));

		$arr = fetch_array($file);
		$this->assertArrayHasKey('test', $arr);
		$this->assertArrayHasKey('test2', $arr);
	}

	/**
	 * @group fetch_array
	 * @depends testFetchArray
	 */
	public function testFetchArrayNoExtension()
	{
		$dir = $this->fetchArrayDir();
		$file = trailingslashit($dir) . 'test';
		$this->assertTrue(is_readable($file . '.php'));

		$arr = fetch_array($file);
		$this->assertArrayHasKey('test', $arr);
		$this->assertArrayHasKey('test2', $arr);
	}

	/**
	 * @group fetch_array
	 * @depends testFetchArrayNoExtension
	 */
	public function testFetchArrayThrowsInvalidArgumentException()
	{
		$dir = $this->fetchArrayDir();
		$file = trailingslashit($dir) . 'test-no-array';
		$this->assertTrue(is_readable($file . '.php'));

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage(sprintf('%s did not return an array.', $file . '.php'));

		$arr = fetch_array($file);
	}

	/**
	 * @group fetch_array
	 * @depends testFetchArrayWithConfig
	 */
	public function testFetchArrayThrowsInvalidConfig()
	{
		$dir = $this->fetchArrayDir();
		$config = $this->createMockConfig();

		$plugin_file = MOCK_PLUGIN_REL_PATH;
		WP_Mock::userFunction('plugin_basename', [
			'args' => $plugin_file,
			'return' => basename($plugin_file)
		]);

		$config
			->expects()
			->get('paths')
			->andReturn(null);

		$config
			->expects()
			->get('plugin_file')
			->andReturn($plugin_file);

		$this->expectException(\JayWolfeLib\Exception\InvalidConfigException::class);
		$this->expectExceptionMessage(
			sprintf('Array path not set for %s', basename($plugin_file))
		);

		$arr = fetch_array('test', $config);
	}

	/**
	 * @group fetch_array
	 * @depends testFetchArrayNoExtension
	 */
	public function testFetchArrayWithConfig()
	{
		$dir = $this->fetchArrayDir();
		$config = $this->createMockConfig();

		$config
			->expects()
			->get('paths')
			->twice()
			->andReturn([
				'arrays' => $dir
			]);

		$arr = fetch_array('test', $config);

		$this->assertArrayHasKey('test', $arr);
		$this->assertArrayHasKey('test2', $arr);
	}

	private function fetchArrayDir(): string
	{
		return MOCK_ARRAY_PATH;
	}
}
