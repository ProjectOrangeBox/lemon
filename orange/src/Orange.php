<?php

declare(strict_types=1);

use dmyers\orange\Event;
use dmyers\orange\Input;
use dmyers\orange\Output;
use dmyers\orange\Router;
use dmyers\orange\Container;
use dmyers\orange\Dispatcher;
use dmyers\orange\exceptions\ConfigFileNotArray;
use dmyers\orange\exceptions\ConfigFolderNotFound;

if (!function_exists('container')) {
	function container()
	{
		return new Container;
	}
}

if (!function_exists('orange')) {
	function orange(string $configFolderPath, ?string $request_uri = null, ?string $request_method = null)
	{
		$container = new Container;

		/* as array */
		$container->config = loadConfig($configFolderPath);

		$container->events = new Event;

		$container->input = new Input($container->config['input']);

		$container->router = new Router($container->config['routes'], $container->input);

		$container->output = new Output($container->config['output'], $container->input);

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
	function logMsg(string $msg, string $level = 'log', bool $skipDate = false): int
	{
		$pre = ($skipDate) ? '' : date(DATE_RFC2822) . ' ';

		return file_put_contents(container()->config['path']['log'] . '/' . $level . '.txt', $pre . $msg . chr(10), FILE_APPEND | LOCK_EX);
	}
}

if (!function_exists('loadConfig')) {
	function loadConfig(string $path): array
	{
		$array = [];

		if (!is_dir($path)) {
			throw new ConfigFolderNotFound($path);
		}

		foreach (glob($path . '/*.php') as $file) {
			$configArray = require $file;

			if (!is_array($configArray)) {
				throw new ConfigFileNotArray($file);
			}

			$array[strtolower(basename($file, '.php'))] = $configArray;
		}

		return $array;
	}
}
