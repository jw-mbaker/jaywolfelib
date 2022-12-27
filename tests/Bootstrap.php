<?php

define('ABSPATH', __DIR__ . DIRECTORY_SEPARATOR . 'dummy-files');

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
WP_Mock::bootstrap();