<?php

declare(strict_types=1);

use dmyers\orange\Event;

return [
	'before.controller' => [
		[\app\libraries\Middleware::class . '::before', Event::PRIORITY_HIGHEST],
		[function (\dmyers\orange\Container &$container, array &$route) {
			$container->output->appendOutput('<pre>hello world' . chr(10));
		}, Event::PRIORITY_HIGHEST],
	],
	'after.controller' => [
		[\app\libraries\Middleware::class . '::after', Event::PRIORITY_HIGHEST],
		[function (\dmyers\orange\Container &$container, ?string &$output) {
			$output .= '<p>Good Bye World!</p>';
		}, Event::PRIORITY_HIGHEST],
	],
	'some_bogus_event$that@doesn\'t&exist' => [
		['\app\bogus\class::bogus_method', Event::PRIORITY_LOWEST],
	],
];
