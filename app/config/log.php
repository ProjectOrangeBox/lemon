<?php

declare(strict_types=1);

use Monolog\Logger;
use dmyers\orange\Log;
use Monolog\Handler\StreamHandler;

$monolog = new Logger('orange');

$monolog->pushHandler(new StreamHandler(__ROOT__ . '/var/logs/' . date('Y-m-d') . '-log.txt'));

return [
	'filepath' => __ROOT__ . '/var/logs/' . date('Y-m-d') . '-log.txt',
	'threshold' => LOG::ALL,
	//'monolog' => $monolog,
];
