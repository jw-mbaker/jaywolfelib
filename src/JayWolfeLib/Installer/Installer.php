<?php

namespace JayWolfeLib\Installer;

use JayWolfeLib\Config\ConfigInterface;
use JayWolfeLib\Hooks\Hooks;
use JayWolfeLib\Traits\JayWolfeTrait;
use JayWolfeLib\Exception\InvalidConfig;

/**
 * Plugin installer class.
 */
class Installer implements InstallerInterface
{
	use JayWolfeTrait;

	/**
	 * The plugin file.
	 *
	 * @var string
	 */
	private $plugin_file;

	public function __construct(ConfigInterface $config)
	{
		$this->set_config($config);

		$this->plugin_file = $config->get('plugin_file');

		Hooks::add_action('plugins_loaded', [$this, 'update_db_check']);
	}

	public function __invoke()
	{
		$this->install();
	}

	public function update_db_check(): void
	{
		if (null === $this->config->get('db')) {
			throw new InvalidConfig('"db" option not set for ' . plugin_basename($this->plugin_file));
		}

		$db = $this->fetch_array('tables');

		$key = sanitize_key($this->config->get('db'));

		$db_version = $db['version'];

		if ( get_option( "{$key}_db_version" ) != $db_version ) {
			$this->install();
		}
	}

	/**
	 * Install the plugin.
	 *
	 * @return void
	 */
	public function install(): void
	{
		global $wpdb;

		if (null === $this->config->get('db')) {
			throw new InvalidConfig('"db" option not set for ' . plugin_basename($this->plugin_file));
		}

		$db = $this->fetch_array('tables');
		$db_version = $db['version'];

		$charset_collate = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$key = sanitize_key($this->config->get('db'));

		add_option( "{$key}_db_version", '1', '', 'no' );

		$installed_ver = get_option( "{$key}_db_version" );

		if ( $installed_ver != $db_version ) {
			$this->create_table($db, $charset_collate);
			update_option( "{$key}_db_version", $db_version );
		}
	}

	/**
	 * Create new or modify existing tables.
	 * 
	 * @param array $db
	 * @param string $charset_collate
	 * 
	 * @return void
	 */
	private function create_table(array $db, string $charset_collate): void
	{
		global $wpdb;

		if (!is_array($db['tables'])) return;

		foreach ($db['tables'] as $table => $fields) {
			if (!is_array($fields)) continue;

			$sql = "CREATE TABLE {$wpdb->prefix}$table";
			$fld = [];
			$fld[] = "id bigint(20) NOT NULL AUTO_INCREMENT";
			foreach ($fields as $field => $structure) {
				$fld[] = "$field {$structure['type']}" .
					(isset($structure['length']) ? "({$structure['length']})" : "") .
					(isset($structure['default']) ? " DEFAULT {$structure['default']}" : "");
			}
			$fld[] = "PRIMARY KEY id (id)";
			$table_fields = implode(", \n", $fld);
			$sql .= " ($table_fields) $charset_collate;";

			dbDelta($sql);
		}
	}
}