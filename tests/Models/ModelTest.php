<?php

namespace JayWolfeLib\Tests\Models;

use JayWolfeLib\Tests\Traits\DevContainerTrait;
use WP_Mock;
use Mockery;

class ModelTest extends \WP_Mock\Tools\TestCase
{
	use DevContainerTrait;

	private $mockModel;
	private $wpdb;

	public function setUp(): void
	{
		$this->wpdb = Mockery::mock(\WPDB::class);
		$this->wpdb->prefix = 'wp_';
		$this->container = $this->createDevContainer();
		$this->container->set(\WPDB::class, $this->wpdb);
		$this->mockModel = $this->container->get(MockModel::class);
		WP_Mock::setUp();
	}

	public function tearDown(): void
	{
		WP_Mock::tearDown();
		Mockery::close();
	}

	/**
	 * @group model
	 */
	public function testCanUpdateData()
	{
		$time = date('Y-m-d H:i:s', time());
		WP_Mock::userFunction('current_time', ['return' => $time]);

		$data = ['mock_column1' => 'test', 'mock_column2' => 123, 'date_updated' => $time];

		$this->wpdb->expects()->update(
			$this->mockModel->table,
			$data,
			['id' => 1]
		);

		$id = $this->mockModel->saveData($data, 1);
		$this->assertEquals(1, $id);
	}

	public function testCanInsertData()
	{
		$time = date('Y-m-d H:i:s', time());
		WP_Mock::userFunction('current_time', ['return' => $time]);

		$data = ['mock_column1' => 'test', 'mock_column2' => 123, 'date_updated' => $time];

		$this->wpdb->expects()
			->insert(
				$this->mockModel->table,
				$data
			)
			->andSet('insert_id', 1);

		$id = $this->mockModel->saveData($data);
		$this->assertEquals(1, $id);
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testCanDeleteData()
	{
		$where = ['id' => '1'];
		$args = ['%d'];

		$this->wpdb->expects()->delete(
			$this->mockModel->table,
			$where,
			$args
		);

		$this->mockModel->deleteData($where, $args);
	}
}