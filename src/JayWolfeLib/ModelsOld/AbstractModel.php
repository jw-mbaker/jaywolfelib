<?php

namespace JayWolfeLib\Models;

use JayWolfeLib\Factory\ModelFactoryInterface;
use JayWolfeLib\Traits\ContainerAwareTrait;
use Psr\Container\ContainerInterface;

abstract class AbstractModel implements ModelInterface
{
	use ContainerAwareTrait;

	/**
	 * The wpdb instance.
	 *
	 * @var \WPDB
	 */
	protected $wpdb;

	/**
	 * The table.
	 *
	 * @var string
	 */
	public $table;

	/**
	 * Constructor.
	 *
	 * @param \WPDB $wpdb
	 * @param string $table
	 */
	public function __construct(\WPDB $wpdb, string $table)
	{
		$this->wpdb = $wpdb;
		$this->table = $wpdb->prefix . $table;
	}

	/**
	 * Saves the data to the specified table.
	 * 
	 * @param array $data
	 * @param int|null $id
	 * 
	 * @return int|bool
	 */
	public function saveData(array $data, int $id = NULL)
	{	
		$data['date_updated'] = current_time('mysql');

		if ($id) {
			$this->wpdb->update(
				$this->table,
				$data,
				['id' => $id]
			);
		} else {
			$this->wpdb->insert(
				$this->table,
				$data
			);
			$id = $this->wpdb->insert_id;
		}

		return $id;
	}

	/**
	 * Deletes a row from the specified table.
	 *
	 * @param array $where
	 * @param array $args
	 * @return void
	 */
	public function deleteData(array $where = [], array $args = [])
	{
		$this->wpdb->delete($this->table, $where, $args);
	}

	/**
	 * Get an object from the container.
	 *
	 * @param string $class
	 * @return mixed
	 */
	protected function get(string $class)
	{
		return $this->container->get($class);
	}
}