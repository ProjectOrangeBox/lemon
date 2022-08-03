<?php

declare(strict_types=1);

define('__ROOT__', realpath(__DIR__ . '/../'));

require __ROOT__ . '/vendor/autoload.php';

/* send config into application */
run(setUpConfig(__ROOT__ . '/app/config/config.php'));
