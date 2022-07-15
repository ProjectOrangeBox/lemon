<?php

declare(strict_types=1);

use dmyers\orange\Log;
use dmyers\orange\Event;
use dmyers\orange\Input;
use dmyers\orange\Config;
use dmyers\orange\Output;
use dmyers\orange\Router;
use dmyers\orange\Dispatcher;

return [
	'config' => Config::class,
	'output' => Output::class,
	'input' => Input::class,
	'dispatcher' => Dispatcher::class,
	'events' => Event::class,
	'log' => Log::class,
	'router' => Router::class,
];
