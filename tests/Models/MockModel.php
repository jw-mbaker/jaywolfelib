<?php

namespace JayWolfeLib\Tests\Models;

use JayWolfeLib\Models\AbstractModel;

class MockModel extends AbstractModel
{
	public function __construct(\WPDB $wpdb)
	{
		parent::__construct($wpdb, 'mock_table');
	}
}