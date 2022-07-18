<?php

declare(strict_types=1);

use dmyers\orange\Log;
use dmyers\orange\Event;
use dmyers\orange\Input;
use dmyers\orange\Config;
use dmyers\orange\Output;
use dmyers\orange\Router;
use dmyers\orange\Container;
use dmyers\orange\Dispatcher;

return [
	'log' => function (Container $container) {
		return new Log($container->config->log);
	},
	'events' => function (Container $container) {
		return new Event();
	},
	'input' => function (Container $container) {
		return new Input($container->config->input);
	},
	'config' => function (Container $container) {
		return new Config($container->get('$configFolderPath'));
	},
	'output' => function (Container $container) {
		return new Output($container->config->output, $container->input);
	},
	'router' => function (Container $container) {
		return new Router($container->config->routes, $container->input);
	},
	'dispatcher' => function (Container $container) {
		return new Dispatcher($container->input, $container->output, $container->config);
	},
];
