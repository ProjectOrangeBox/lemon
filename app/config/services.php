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
	'log' => function (array $config) {
		return new Log($config);
	},
	'events' => function () {
		return new Event();
	},
	'input' => function (array $config) {
		return new Input($config);
	},
	'config' => function (string $configFolder) {
		return new Config($configFolder);
	},
	'output' => function (array $config, input $input) {
		return new Output($config, $input);
	},
	'router' => function (array $config, input $input) {
		return new Router($config, $input);
	},
	'dispatcher' => function (input $input, output $output, config $config) {
		return new Dispatcher($input, $output, $config);
	},
];
