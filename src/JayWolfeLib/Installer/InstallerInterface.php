<?php

namespace JayWolfeLib\Installer;

interface InstallerInterface
{
	public function install();
	public function update_db_check();
}