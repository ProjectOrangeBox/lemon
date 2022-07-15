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
use dmyers\orange\exceptions\ClassNotFound;
use dmyers\orange\exceptions\ConfigNotFound;
use dmyers\orange\exceptions\ServiceNotFound;

if (!function_exists('orange')) {
	function orange()
	{
		return new Container;
	}
}

if (!function_exists('run')) {
	function run(string $configFolderPath, ?string $request_uri = null, ?string $request_method = null)
	{
		$services = $configFolderPath . '/services.php';

		if (!file_exists($services)) {
			throw new ConfigNotFound('could not locate services');
		}

		$container = orange();

		$container->services = require $services;

		/* as array */
		$container->config = new $container->services['config']($configFolderPath);

		$container->log = new $container->services['log']($container->config->log);

		$container->events = new $container->services['events']();

		$container->input = new $container->services['input']($container->config->input);

		$container->router = new $container->services['router']($container->config->routes, $container->input);

		$container->output = new $container->services['output']($container->config->output, $container->input);

		$container->dispatcher = new $container->services['dispatcher']($container->input, $container->output);

		/* away we go */
		$container->output->appendOutput($container->dispatcher->call($container->router->route($request_uri, $request_method)))->send();
	}
}

if (!function_exists('createService')) {
	function createService(string $name)
	{
		$container = orange();

		if (!isset($container->services[$name])) {
			throw new ServiceNotFound($name);
		}

		$args = func_get_args();

		switch ($args) {
			case 2:
				return new $container->services[$name]($args[0]);
				break;
			case 3:
				return new $container->services[$name]($args[0], $args[1]);
				break;
			case 4:
				return new $container->services[$name]($args[0], $args[1], $args[2]);
				break;
			case 5:
				return new $container->services[$name]($args[0], $args[1], $args[2], $args[3]);
				break;
			default:
				return new $container->services[$name]($container);
		}
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
	function logMsg(string $msg, string $level = 'log')
	{
		orange()->log->writeLog($level, $msg);
	}
}
