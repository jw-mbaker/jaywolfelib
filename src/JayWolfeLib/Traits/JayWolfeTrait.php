<?php

namespace JayWolfeLib\Traits;

use JayWolfeLib\Config\ConfigTrait;
use JayWolfeLib\Exception\InvalidConfig;

trait JayWolfeTrait
{
	use ConfigTrait;

	protected function fetch_array(string $file): array
	{
		if  (null === $this->config) {
			throw new \Exception("Config not set.");
		}

		if (null === $this->config->get('paths')['arrays']) {
			throw new InvalidConfig("Array path not set for " . plugin_basename($this->config->get('plugin_file')) . ".");
		}

		$pathinfo = pathinfo($file);
		if (!isset($pathinfo['extension']) || $pathinfo['extension'] !== 'php') {
			$file .= '.php';
		}
	
		$dir = trailingslashit( $this->config->get('paths')['arrays'] );
	
		$arr = [];
		if (is_readable($dir . $file)) {
			$arr = include $dir . $file;
		}
	
		if (@!is_array($arr)) {
			throw new InvalidConfig("$file did not return an array.");
		}
	
		return $arr;
	}

/**
 * Print log in the log directory.
 *
 * @param string $message
 * @return void
 */
protected function error_log(string $message)
{
	if (null === $this->config) {
		throw new \Exception("Config not set.");
	}

	if (null === $this->config->get('paths')['log']) {
		throw new InvalidConfig("Log path not set for " . plugin_basename($this->config->get('plugin_file')) . ".");
	}

	$log_path = $this->config->get('paths')['log'];

	if (!is_dir($log_path)) {
		mkdir($log_path, 0755, true);
	}

	\error_log($message . PHP_EOL, 3, trailingslashit($log_path) . 'log.txt');
}
}