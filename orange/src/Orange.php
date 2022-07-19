<?php

declare(strict_types=1);

use dmyers\orange\Container;
use dmyers\orange\exceptions\ConfigFileNotFound;
use dmyers\orange\exceptions\InvalidConfigurationValue;

if (!function_exists('run')) {
	function run(array $config, ?string $request_uri = null, ?string $request_method = null)
	{
		if (!isset($config['services'])) {
			throw new InvalidConfigurationValue('services');
		}

		if (!file_exists($config['services'])) {
			throw new ConfigFileNotFound($config['services']);
		}

		$serviceArray = require $config['services'];

		if (!is_array($serviceArray)) {
			throw new InvalidConfigurationValue('Not an array of services');
		}

		$container = new Container($serviceArray);

		$container->{'$config'} = $config;

		$container->events->trigger('before.router', $container);

		$route = $container->router->route($request_uri, $request_method);

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
