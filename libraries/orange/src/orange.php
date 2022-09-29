<?php

declare(strict_types=1);

use dmyers\orange\Container;
use dmyers\orange\exceptions\ConfigNotFound;
use dmyers\orange\exceptions\InvalidConfigurationValue;

define('NOVALUE', '__#NOVALUE#__');

if (!function_exists('http')) {
	/**
	 * Method run
	 *
	 * @param array $config [explicite description]
	 *
	 * @return void
	 */
	function http(array $config = []): Container
	{
		define('DEBUG', env('DEBUG', false));
		define('ENVIRONMENT', env('ENVIRONMENT', 'production'));

		/* user custom loader */
		if (file_exists(__ROOT__ . '/app/Bootstrap.php')) {
			require_once __ROOT__ . '/app/Bootstrap.php';
		}

		$container = container($config['services'] ?? '');

		$container->{'$config'} = $config;

		$container->events->trigger('before.router', $container);

		$route = $container->router->match($container->input->requestUri(), $container->input->requestMethod());

		$container->events->trigger('before.controller', $container, $route);

		$output = $container->dispatcher->call($route);

		$container->events->trigger('after.controller', $container, $output);

		$container->output->appendOutput($output)->send();

		$container->events->trigger('after.output', $container);

		return $container;
	}
}

if (!function_exists('cli')) {
	function cli(array $config = []): Container
	{
		define('DEBUG', env('DEBUG', true));
		define('ENVIRONMENT', env('ENVIRONMENT', 'testing'));

		/* user custom loader */
		if (file_exists(__ROOT__ . '/cli/Bootstrap.php')) {
			require_once __ROOT__ . '/cli/Bootstrap.php';
		}

		$container = container($config['services'] ?? '');

		$container->{'$config'} = $config;

		return $container;
	}
}

if (!function_exists('container')) {
	function container(string $servicesFile = null): Container
	{
		$serviceArray = null;

		if (isset($servicesFile) && file_exists($servicesFile)) {
			$serviceArray = require_once $servicesFile;

			if (!is_array($serviceArray)) {
				throw new InvalidConfigurationValue('Not an array of services');
			}
		}

		return new Container($serviceArray);
	}
}

if (!function_exists('exceptionHandler')) {
	/**
	 * Method exceptionHandler
	 *
	 * @param \Throwable $exception [explicite description]
	 *
	 * @return void
	 */
	function exceptionHandler(\Throwable $exception)
	{
		$classes = explode('\\', get_class($exception));

		echo '<pre>' . ucwords(trim(implode(' ', preg_split('/(?=[A-Z])/', end($classes)))) . chr(10)) . '"' . $exception->getMessage() . '"' . chr(10) . 'thrown on line ' . $exception->getLine() . ' in ' . $exception->getFile() . chr(10);
	}

	set_exception_handler('exceptionHandler');
}

if (!function_exists('logMsg')) {
	/**
	 * Method logMsg
	 *
	 * @param string $msg [explicite description]
	 * @param string $level [explicite description]
	 *
	 * @return void
	 */
	function logMsg(string $msg, string $level = 'INFO')
	{
		container()->log->writeLog($level, $msg);
	}
}

if (!function_exists('env')) {
	/**
	 * Method env
	 *
	 * Get a environmental variable with support for default
	 *
	 * @param $key string environmental variable you want to load
	 * @param $default mixed the default value if environmental variable isn't set
	 *
	 * @return mixed
	 */
	function env(string $key, $default = NOVALUE) /* mixed */
	{
		static $env = [];

		/* setp only the first time */
		if (!$env) {
			$env = $_ENV;

			if (file_exists(__ROOT__ . '/.env')) {
				$env = array_replace($env, parse_ini_file(__ROOT__ . '/.env', true, INI_SCANNER_TYPED));
			}
		}

		if (!isset($env[$key]) && $default === NOVALUE) {
			throw new ConfigNotFound('The environmental variable "' . $key . '" is not set and no default was provided.');
		}

		return (isset($env[$key])) ? $env[$key] : $default;
	}
}

if (!function_exists('siteUrl')) {
	function siteUrl(bool $autoDetect = true): string
	{
		$container = container();

		$config = $container->{'$config'};

		if (!isset($config['siteUrl'])) {
			throw new ConfigNotFound('File: config.php Value: siteUrl');
		}

		$siteUrl = $config['siteUrl'];

		if ($autoDetect) {
			$siteUrl = 'http' . ($container->input->isHttpsRequest() ? 's' : '') . '://' . $siteUrl;
		}

		return $siteUrl;
	}
}
