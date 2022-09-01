<?php

declare(strict_types=1);

namespace dmyers\orange;

use dmyers\orange\exceptions\ControllerClassNotFound;
use dmyers\orange\exceptions\ControllerMethodNotFound;

class Dispatcher
{
	protected $input = null;
	protected $output = null;

	public function __construct(input &$input, output &$output, config &$config)
	{
		$this->input = $input;
		$this->output = $output;
		$this->config = $config;
	}

	public function call(array $route): ?string
	{
		$controllerClass = $route['controller'];

		if (class_exists($controllerClass)) {

			$method = $route['method'];

			if (method_exists($controllerClass, $method)) {
				/* we found something */
				$matches = array_map(function ($value) {
					return urldecode($value);
				}, $route['args']);

				return (new $controllerClass($this->input, $this->output, $this->config))->$method(...$matches);
			} else {
				throw new ControllerMethodNotFound($method);
			}
		} else {
			throw new ControllerClassNotFound($controllerClass);
		}
	}
} /* end class */
