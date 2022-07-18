<?php

declare(strict_types=1);

return [
	/* home page */
	['method' => '*', 'url' => '/', 'callback' => [\app\controllers\MainController::class, 'index']],

	/* 'test/([a-z]+)/(\d+)' */
	['method' => 'GET', 'url' => '/test/([a-z]+)/(\d+)', 'callback' => [\app\controllers\TestController::class, 'index']],

	/* 404 catch all */
	['method' => '*', 'url' => '(.*)', 'callback' => [\app\controllers\FourohfourController::class, 'index']],
];
