<?php

declare(strict_types=1);

return [
	/* home page */
	['method' => '*', 'url' => '/', 'callback' => [\app\controllers\MainController::class, 'index'], 'name' => 'home'],

	['method' => 'GET', 'url' => '/test/bar', 'callback' => [\app\controllers\TestController::class, 'bar']],
	['method' => 'GET', 'url' => '/test/disc', 'callback' => [\app\controllers\TestController::class, 'disc']],

	['method' => 'GET', 'url' => '/test', 'callback' => [\app\controllers\TestController::class, 'foo']],

	/* 'test/([a-z]+)/(\d+)' */
	['method' => 'GET', 'url' => '/test/([a-z]+)/(\d+)', 'callback' => [\app\controllers\TestController::class, 'index'], 'name' => 'test'],

	/* 404 catch all */
	['method' => '*', 'url' => '(.*)', 'callback' => [\app\controllers\FourohfourController::class, 'index']],

	['url' => '/assets', 'name' => 'assets'],
	['url' => '/product/([a-z]+)/(\d+)', 'name' => 'product'],
	['url' => '/assets/js', 'name' => 'javascript'],
	['url' => '/assets/css', 'name' => 'css'],
	['url' => '/images', 'name' => 'images'],
];
