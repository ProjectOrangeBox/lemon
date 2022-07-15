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

if (!function_exists('orange')) {
	function orange()
	{
		return new Container;
	}
}

if (!function_exists('run')) {
	function run(string $configFolderPath, ?string $request_uri = null, ?string $request_method = null)
	{
		$container = orange();

		/* as array */
		$container->config = new Config($configFolderPath);

		$container->log = new Log($container->config->log);

		$container->events = new Event();

		$container->input = new Input($container->config->input);

		$container->router = new Router($container->config->routes, $container->input);

		$container->output = new Output($container->config->output, $container->input);

		$container->dispatcher = new Dispatcher($container->input, $container->output);

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
		orange()->log->writeLog($level, $msg);
	}
}
