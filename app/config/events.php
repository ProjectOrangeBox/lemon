<?php

declare(strict_types=1);

return [
	'before.controller' => [
		[\app\libraries\Middleware::class, 'before'],
	],
	'after.controller' => [
		[\app\libraries\Middleware::class, 'after'],
	]
];
