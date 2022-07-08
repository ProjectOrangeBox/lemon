<?php

declare(strict_types=1);

use dmyers\orange\Event;
use dmyers\orange\Input;
use dmyers\orange\Output;
use dmyers\orange\Router;
use dmyers\orange\Container;
use dmyers\orange\Dispatcher;
use dmyers\orange\ViewNotFound;
use dmyers\orange\ConfigFileNotArray;
use dmyers\orange\ConfigFolderNotFound;
use dmyers\orange\ContainerItemNotFound;

if (!function_exists('orange')) {
	function orange(string $configFolderPath, ?string $request_uri = null, ?string $request_method = null)
	{
		/* as array */
		app()->config = loadConfig($configFolderPath);

		app()->events = new Event;

		app()->input = new Input(app('config')['input']);

		app()->router = new Router(app('config')['routes'], app()->input);

		app()->output = new Output(app('config')['output'], app()->input);

		app()->dispatcher = new Dispatcher(app()->input, app()->output);

		/* away we go */
		app()->output->appendOutput(app()->dispatcher->call(app()->router->route($request_uri, $request_method)))->send();
	}
}

if (!function_exists('app')) {
	function app(string $name = null)
	{
		static $container;

		if (!$container) {
			$container = new Container;
		}

		if ($name && !$container->$name) {
			throw new ContainerItemNotFound($name);
		}

		return ($name) ? $container->$name : $container;
	}
}

if (!function_exists('exceptionHandler')) {
	function exceptionHandler(\Throwable $exception)
	{
		echo '<pre>' . trim(implode(' ', preg_split('/(?=[A-Z])/', get_class($exception)))) . ' exception "' . $exception->getMessage() . '"' . chr(10) . 'thrown on line ' . $exception->getLine() . ' in ' . $exception->getFile() . chr(10);
	}

	set_exception_handler('exceptionHandler');
}

if (!function_exists('logMsg')) {
	function logMsg(string $msg, string $level = 'log', bool $skipDate = false): int
	{
		$pre = ($skipDate) ? '' : date(DATE_RFC2822) . ' ';

		return file_put_contents(app('config')['path']['log'] . '/' . $level . '.txt', $pre . $msg . chr(10), FILE_APPEND | LOCK_EX);
	}
}

if (!function_exists('view')) {
	function view($_mvc_view_name, $_mvc_view_data = [])
	{
		/* what file are we looking for? */
		$_mvc_view_file = rtrim(app('config')['path']['views'], '/') . '/' . $_mvc_view_name . '.php';

		/* is it there? if not return nothing */
		if (!file_exists($_mvc_view_file)) {
			/* file not found so bail */
			throw new ViewNotFound($_mvc_view_name);
		}

		/* extract out view data and make it in scope */
		extract($_mvc_view_data, EXTR_OVERWRITE);

		/* start output cache */
		ob_start();

		/* load in view (which now has access to the in scope view data */
		require $_mvc_view_file;

		/* capture cache and return */
		return ob_get_clean();
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
