<?php

namespace JayWolfeLib\Models;

use JayWolfeLib\Factory\ModelFactoryInterface;

abstract class Model implements ModelInterface
{
	/**
	 * The wpdb instance.
	 *
	 * @var \WPDB
	 */
	protected $wpdb;

	/**
	 * The model factory.
	 *
	 * @var ModelFactoryInterface
	 */
	protected $factory;

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
	 * @param ModelFactoryInterface $factory
	 * @param string $table
	 */
	public function __construct(\WPDB $wpdb, ModelFactoryInterface $factory, string $table)
	{
		$this->wpdb = $wpdb;
		$this->factory = $factory;
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
	protected function saveData(array $data, ?int $id = NULL)
	{	
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
	protected function deleteData(array $where = [], array $args = [])
	{
		$this->wpdb->delete($this->table, $where, $args);
	}
}