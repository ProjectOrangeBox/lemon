<?php

declare(strict_types=1);

namespace dmyers\orange;

use dmyers\orange\Event;
use dmyers\orange\Input;
use dmyers\orange\Config;
use dmyers\orange\Output;
use dmyers\orange\Router;
use dmyers\orange\Dispatcher;

class Orange
{
	protected $configFolderPath = '';

	public function __construct(string $configFolderPath)
	{
		$this->configFolderPath = $configFolderPath;

		/* a few global helpers */
		require 'OrangeHelpers.php';
	}

	public function go(?string $request_uri = null, ?string $request_method = null)
	{
		/* as array */
		app()->config = loadConfig($this->configFolderPath);

		app()->events = new Event;

		app()->input = new Input(app('config')['input']);

		app()->router = new Router(app('config')['routes'], app()->input);

		app()->output = new Output(app('config')['output'], app()->input);

		app()->dispatcher = new Dispatcher(app()->input, app()->output);

		/* away we go */
		app()->output->appendOutput(app()->dispatcher->call(app()->router->route($request_uri, $request_method)))->send();
	}
} /* end class */