<?php

namespace JayWolfeLib\Installer;

trait InstallerTrait
{
	/**
	 * The installer object.
	 *
	 * @var InstallerInterface
	 */
	protected $installer;

	public function set_installer(InstallerInterface $installer)
	{
		$this->installer = $installer;
	}

	public function get_installer(): InstallerInterface
	{
		return $this->installer;
	}
}