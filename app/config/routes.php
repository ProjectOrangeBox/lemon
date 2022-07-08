<?php

declare(strict_types=1);

return [
	/* home page */
	['method' => '*', 'url' => '/', 'callback' => ['/app/controllers/MainController', 'index']],

	['method' => 'GET', 'url' => '/library.js', 'callback' => ['/app/controllers/PullController', 'getLibs']],
	['method' => 'POST', 'url' => '/post/data', 'callback' => ['/app/controllers/PushController', 'postData']],

	/* 'test/([a-z]+)/(\d+)' */
	['method' => 'GET', 'url' => '/test/([a-z]+)/(\d+)', 'callback' => ['/app/controllers/TestController', 'index']],

	/* 404 catch all */
	['method' => '*', 'url' => '(.*)', 'callback' => ['/app/controllers/FourohfourController', 'index']],
];
