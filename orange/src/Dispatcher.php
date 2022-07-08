<?php

declare(strict_types=1);

namespace dmyers\orange;

use dmyers\orange\exceptions\ControllerFileNotFound;
use dmyers\orange\exceptions\ControllerClassNotFound;
use dmyers\orange\exceptions\ControllerMethodNotFound;

class Dispatcher
{
	protected $input = null;
	protected $output = null;

	public function __construct(input &$input, output &$output)
	{
		$this->input = $input;
		$this->output = $output;
	}

	public function call(array $route): ?string
	{
		$controllerFile = __ROOT__ . $route['controller'] . '.php';

		if (file_exists($controllerFile)) {
			require_once $controllerFile;

			$controllerClass = basename($route['controller']);

			if (class_exists($controllerClass, false)) {

				$method = $route['method'];

				if (method_exists($controllerClass, $method)) {
					/* we found something */
					$matches = array_map(function ($value) {
						return urldecode($value);
					}, $route['args']);

					return (new $controllerClass($this->input, $this->output))->$method(...$matches);
				} else {
					throw new ControllerMethodNotFound($method);
				}
			} else {
				throw new ControllerClassNotFound($controllerClass);
			}
		} else {
			throw new ControllerFileNotFound($controllerFile);
		}
	}
} /* end class */
