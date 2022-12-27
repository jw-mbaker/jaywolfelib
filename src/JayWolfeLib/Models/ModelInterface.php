<?php

namespace JayWolfeLib\Models;

interface ModelInterface
{
	public function saveData(array $data, int $id = NULL);
	public function deleteData(array $where = [], array $args = []);
}