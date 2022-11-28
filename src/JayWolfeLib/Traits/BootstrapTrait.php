<?php

namespace JayWolfeLib\Traits;

use JayWolfeLib\Config\ConfigTrait;

trait BootstrapTrait
{
	use ConfigTrait;

	public function deactivate_die(string $message)
	{
		$plugin_file = $this->config->get('plugin_file');

		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		deactivate_plugins( plugin_basename($plugin_file) );

		wp_die( wp_kses_post($message) );
	}

	private function check_requirements()
	{
		if (!$this->config->requirements_met()) {
			ob_start();
			$errors = $this->config->get_errors();
			foreach ($errors as $error):
			?>
			<div><?=$error->error_message?> (<?=$error->info?>)</div>
			<?php
			endforeach;
	
			$this->deactivate_die(ob_get_clean());
		}
	}
}