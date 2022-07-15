<?php

declare(strict_types=1);

use dmyers\orange\Container;
use dmyers\orange\exceptions\ConfigNotFound;

if (!function_exists('run')) {
	function run(string $configFolderPath, ?string $request_uri = null, ?string $request_method = null)
	{
		$services = $configFolderPath . '/services.php';

		if (!file_exists($services)) {
			throw new ConfigNotFound('could not locate services');
		}

		$container = new Container;

		$container->services = require $services;

		$container->config = $container->services['config']($configFolderPath);

		$container->log = $container->services['log']($container->config->log);

		$container->event = $container->services['events']();

		$container->input = $container->services['input']($container->config->input);

		$container->router = $container->services['router']($container->config->routes, $container->input);

		$container->output = $container->services['output']($container->config->output, $container->input);

		$container->dispatcher = $container->services['dispatcher']($container->input, $container->output, $container->config);

		/* away we go */
		$container->output->appendOutput($container->dispatcher->call($container->router->route($request_uri, $request_method)))->send();
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
		(new \dmyers\orange\Container)->log->writeLog($level, $msg);
	}
}
