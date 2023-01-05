<?php

namespace JayWolfeLib;

define('ABSPATH', __DIR__ . DIRECTORY_SEPARATOR . 'dummy-files');
define('WP_DEBUG', true);
define('MOCK_PLUGIN_PATH', ABSPATH . DIRECTORY_SEPARATOR . 'mock-plugin');
define('MOCK_PLUGIN_REL_PATH', MOCK_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'mock-plugin-file.php');
define('MOCK_CONFIG_FILE', ABSPATH . DIRECTORY_SEPARATOR . 'mock-config.php');
define('MOCK_ARRAY_PATH', ABSPATH . DIRECTORY_SEPARATOR . 'arrays');
define('MOCK_TEMPLATE_PATH', ABSPATH . DIRECTORY_SEPARATOR . 'templates');
define(__NAMESPACE__ . '\\PRODUCTION', false);