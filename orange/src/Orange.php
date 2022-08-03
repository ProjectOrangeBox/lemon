<?php

declare(strict_types=1);

use dmyers\orange\Container;
use dmyers\orange\exceptions\ConfigNotFound;
use dmyers\orange\exceptions\ConfigFileNotFound;
use dmyers\orange\exceptions\InvalidConfigurationValue;

define('NOVALUE', '__#NOVALUE#__');

if (!function_exists('run')) {
	function run(array $config)
	{
		define('DEBUG', env('DEBUG', false));
		define('ENVIRONMENT', env('ENVIRONMENT', 'production'));

		/* user custom loader */
		if (file_exists(__ROOT__ . '/app/Bootstrap.php')) {
			require_once __ROOT__ . '/app/Bootstrap.php';
		}

		if (!isset($config['services'])) {
			throw new InvalidConfigurationValue('services');
		}

		if (!file_exists($config['services'])) {
			throw new ConfigFileNotFound($config['services']);
		}

		$serviceArray = require_once $config['services'];

		if (!is_array($serviceArray)) {
			throw new InvalidConfigurationValue('Not an array of services');
		}

		$container = new Container($serviceArray);

		$container->{'$config'} = $config;

		$container->events->trigger('before.router', $container);

		$route = $container->router->route($container->input->requestUri(), $container->input->requestMethod());

		$container->events->trigger('before.controller', $container, $route);

		$output = $container->dispatcher->call($route);

		$container->events->trigger('after.controller', $container, $output);

		$container->output->appendOutput($output)->send();

		$container->events->trigger('after.output', $container);
	}
}

if (!function_exists('exceptionHandler')) {
	function exceptionHandler(\Throwable $exception)
	{
		$classes = explode('\\', get_class($exception));

		echo '<pre>' . trim(implode(' ', preg_split('/(?=[A-Z])/', end($classes)))) . chr(10) . '"' . $exception->getMessage() . '"' . chr(10) . 'thrown on line ' . $exception->getLine() . ' in ' . $exception->getFile() . chr(10);
	}

	set_exception_handler('exceptionHandler');
}

if (!function_exists('logMsg')) {
	function logMsg(string $msg, string $level = 'INFO')
	{
		(new Container)->log->writeLog($level, $msg);
	}
}

/**
 * get a environmental variable with support for default
 *
 * @param $key string environmental variable you want to load
 * @param $default mixed the default value if environmental variable isn't set
 *
 * @return string
 *
 * @throws \Exception
 *
 * #### Example
 * ```
 * $foo = env('key');
 * $foo = env('key2','default value');
 * ```
 */
if (!function_exists('env')) {
	function env(string $key, $default = NOVALUE)
	{
		if (!isset($_ENV[$key]) && $default === NOVALUE) {
			throw new ConfigNotFound('The environmental variable "' . $key . '" is not set and no default was provided.');
		}

		return (isset($_ENV[$key])) ? $_ENV[$key] : $default;
	}
}
