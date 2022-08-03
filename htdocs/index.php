<?php

declare(strict_types=1);

define('__ROOT__', realpath(__DIR__ . '/../'));

require __ROOT__ . '/vendor/autoload.php';

/* get local .env and merge with $_ENV */
$env = (file_exists(__ROOT__ . '/.env')) ? parse_ini_file(__ROOT__ . '/.env', true, INI_SCANNER_TYPED) : [];
$_ENV = array_replace($_ENV, $env);

/* merge any $_ENV over values in config */
$config = array_replace(require __ROOT__ . '/app/config/config.php', $_ENV);

/* send config into application */
run($config);
