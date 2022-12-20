<?php

namespace JayWolfeLib;

use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder(Container::class);
if (defined("JayWolfeLib\\PRODUCTION") && PRODUCTION) {
	$containerBuilder->enableCompilation(__DIR__ . '/cache');
}

(new Init($containerBuilder))->run();