<?php

declare(strict_types=1);

define('DEBUG', true);
define('__ROOT__', realpath(__DIR__ . '/../'));
ini_set('memory_limit', '4G');

require __ROOT__ . '/vendor/autoload.php';

/* user custom loader */
require __ROOT__ . '/app/Bootstrap.php';

run(__ROOT__ . '/app/config');
