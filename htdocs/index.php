<?php

declare(strict_types=1);

define('__ROOT__', realpath(__DIR__ . '/../'));

require __ROOT__ . '/vendor/autoload.php';

$env = (file_exists(__ROOT__ . '/.env')) ? parse_ini_file(__ROOT__ . '/.env', true, INI_SCANNER_TYPED) : [];
$_ENV = array_replace($_ENV, $env);
$config = array_replace(require __ROOT__ . '/app/config/config.php', $_ENV);

define('DEBUG', env('DEBUG', false));
define('ENVIRONMENT', env('ENVIRONMENT', 'production'));

/* user custom loader */
if (file_exists(__ROOT__ . '/app/Bootstrap.php')) {
	require __ROOT__ . '/app/Bootstrap.php';
}

run($config);
