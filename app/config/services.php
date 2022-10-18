<?php

declare(strict_types=1);

use Adbar\Dot;
use app\libraries\Foo;
use dmyers\orange\Log;
use dmyers\orange\Event;
use dmyers\orange\Input;
use dmyers\orange\Config;
use dmyers\orange\Output;
use dmyers\orange\Router;
use dmyers\orange\Container;
use dmyers\orange\Dispatcher;
use dmyers\orange\exceptions\ConfigFolderNotFound;

return [
	'log' => function (Container $container) {
		return new Log($container->config->log);
	},
	'events' => function (Container $container) {
		return new Event($container->config->events);
	},
	'input' => function (Container $container) {
		return new Input($container->config->input);
	},
	'config' => function (Container $container) {
		return new Config($container->{'$config'}['config folder']);
	},
	'output' => function (Container $container) {
		return new Output($container->config->output, $container->input);
	},
	'router' => function (Container $container) {
		return new Router($container->config->routes);
	},
	'dispatcher' => function (Container $container) {
		return new Dispatcher($container->input, $container->output, $container->config);
	},

	'configDot' => function (Container $container) {
		$path = $container->{'$config'}['config folder'];

		if (!is_dir($path)) {
			throw new ConfigFolderNotFound($path);
		}

		$configs = [];

		foreach (glob($path . '/*.php') as $file) {
			$configs[basename($file, '.php')] = require $file;
		}

		return new Dot($configs);
	},

	'@cat' => 'foo',
	'@dog' => 'bar',

	/* inside array = factory (ie. multiple) */
	'foo[]' => function (Container $container) {
		return new Foo;
	},
	/* not inside = singleton */
	'bar' => function (Container $container) {
		return new Foo;
	},
	'$test' => 'This is a test',
];
