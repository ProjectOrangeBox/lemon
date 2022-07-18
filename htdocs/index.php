<?php

declare(strict_types=1);

define('DEBUG', true);
define('__ROOT__', realpath(__DIR__ . '/../'));
ini_set('memory_limit', '4G');

require __ROOT__ . '/vendor/autoload.php';

/* user custom loader */
if (file_exists(__ROOT__ . '/app/Bootstrap.php')) {
	require __ROOT__ . '/app/Bootstrap.php';
}

$env = (file_exists(__ROOT__ . '/.env')) ? parse_ini_file(__ROOT__ . '/.env', true, INI_SCANNER_TYPED) : [];
$config = array_replace(require __ROOT__ . '/app/config/config.php', $env);

run($config);
