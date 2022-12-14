<?php

namespace JayWolfeLib;

define('ABSPATH', __DIR__ . DIRECTORY_SEPARATOR . 'dummy-files');
define('WP_DEBUG', true);
define(__NAMESPACE__ . '\\MOCK_PLUGIN_PATH', ABSPATH . DIRECTORY_SEPARATOR . 'mock-plugin');
define(__NAMESPACE__ . '\\MOCK_PLUGIN_REL_PATH', MOCK_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'mock-plugin-file.php');
define(__NAMESPACE__ . '\\MOCK_CONFIG_FILE', ABSPATH . DIRECTORY_SEPARATOR . 'mock-config.php');
define(__NAMESPACE__ . '\\MOCK_ARRAY_PATH', ABSPATH . DIRECTORY_SEPARATOR . 'arrays');
define(__NAMESPACE__ . '\\MOCK_TEMPLATE_PATH', ABSPATH . DIRECTORY_SEPARATOR . 'templates');
define(__NAMESPACE__ . '\\PRODUCTION', false);