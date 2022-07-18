<?php

declare(strict_types=1);

use dmyers\orange\Container;
use dmyers\orange\exceptions\InvalidValue;
use dmyers\orange\exceptions\ConfigNotFound;
use dmyers\orange\exceptions\ConfigFileNotFound;

if (!function_exists('run')) {
	function run(array $configArray, ?string $request_uri = null, ?string $request_method = null)
	{
		if (!isset($configArray['services'])) {
			throw new ConfigNotFound('services');
		}

		if (!file_exists($configArray['services'])) {
			throw new ConfigFileNotFound($configArray['services']);
		}

		$serviceArray = require $configArray['services'];

		if (!is_array($serviceArray)) {
			throw new InvalidValue($configArray['services']);
		}

		$container = new Container($serviceArray);

		if (!isset($configArray['config folder'])) {
			throw new ConfigNotFound('config folder');
		}

		$container->reference('$configFolderPath', $configArray['config folder']);

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
		(new Container)->log->writeLog($level, $msg);
	}
}
