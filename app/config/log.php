<?php

declare(strict_types=1);

use dmyers\orange\Log;


return [
	'filepath' => __ROOT__ . '/var/logs/' . date('Y-m-d') . '-log.txt',
	'threshold' => LOG::ALL,
];
