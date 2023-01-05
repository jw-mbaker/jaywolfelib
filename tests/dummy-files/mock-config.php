<?php

return [
	'plugin_file' => MOCK_PLUGIN_REL_PATH,
	'db' => 'mock',
	'paths' => [
		'arrays' => MOCK_ARRAY_PATH,
		'templates' => MOCK_TEMPLATE_PATH
	],
	'dependencies' => [
		'min_php_version' => '7.4',
		'min_wordpress_version' => '5.4',
		'plugins' => [
			'mock/plugin.php'
		]
	]
];