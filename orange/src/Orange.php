<?php

declare(strict_types=1);

use dmyers\orange\Container;
use dmyers\orange\exceptions\ConfigFileNotFound;
use dmyers\orange\exceptions\InvalidConfigurationValue;

if (!function_exists('run')) {
	function run(array $configArray, ?string $request_uri = null, ?string $request_method = null)
	{
		if (!isset($configArray['services'])) {
			throw new InvalidConfigurationValue('services');
		}

		if (!file_exists($configArray['services'])) {
			throw new ConfigFileNotFound($configArray['services']);
		}

		$serviceArray = require $configArray['services'];

		if (!is_array($serviceArray)) {
			throw new InvalidConfigurationValue('services is not an array of services');
		}

		$container = new Container($serviceArray);

		$container->reference('$config', $configArray);

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
	function logMsg(string $msg, string $level = 'INFO')
	{
		(new Container)->log->writeLog($level, $msg);
	}
}
