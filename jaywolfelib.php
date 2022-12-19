<?php

/**
 * Plugin Name: Jay Wolfe Lib
 * Plugin URI: https://github.com/jw-mbaker/jaywolfelib
 * Description: This file loads this library as a must-use plugin.
 * Version: 2.0.2
 * Author: Matthew Baker
 * GitHub Plugin URI: https://github.com/jw-mbaker/jaywolfelib
 * Requires at least: 5.4
 * Requires PHP: 7.4
 */

namespace JayWolfeLib;

/**
 * Whether in production or development environment.
 * 
 * @var bool
 */
const PRODUCTION = true;

require_once __DIR__ . '/jaywolfelib/vendor/autoload.php';