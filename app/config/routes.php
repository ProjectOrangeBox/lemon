<?php

declare(strict_types=1);

return [
	/* home page */
	['method' => '*', 'url' => '/', 'callback' => [\app\controllers\MainController::class, 'index']],

	['method' => 'GET', 'url' => '/test', 'callback' => [\app\controllers\TestController::class, 'foo']],

	/* 'test/([a-z]+)/(\d+)' */
	['method' => 'GET', 'url' => '/test/([a-z]+)/(\d+)', 'callback' => [\app\controllers\TestController::class, 'index']],

	/* 404 catch all */
	['method' => '*', 'url' => '(.*)', 'callback' => [\app\controllers\FourohfourController::class, 'index']],
];
