<?php

namespace JayWolfeLib\Factory;

interface BaseFactoryInterface
{
	/**
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key);
}