<?php

namespace JayWolfeLib\Tests\Models;

use JayWolfeLib\Models\Model;
use JayWolfeLib\Factory\ModelFactoryInterface;

class MockClass extends Model
{
	public function __construct(\WPDB $wpdb, ModelFactoryInterface $factory)
	{
		parent::__construct($wpdb, $factory, 'mock');
	}
}