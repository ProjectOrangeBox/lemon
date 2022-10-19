<?php

declare(strict_types=1);

namespace app\libraries;

use dmyers\orange\Input;
use dmyers\orange\Output;
use dmyers\orange\Router;

class Middleware
{
	public function beforeRouter(Input &$input)
	{
		logMsg('Event: ' . __FUNCTION__);
		logMsg($input->requestUri());
		logMsg($input->requestMethod());
	}

	public function beforeController(Router &$router, Input &$input)
	{
		logMsg('Event: ' . __FUNCTION__);
		logMsg(print_r($router->getMatched(), true));
		logMsg($input->requestUri());
		logMsg($input->requestMethod());

		$route = $router->getMatched();

		if (substr($route['requestURI'], 0, 5) == '/test' && $route['requestMethod'] == 'GET' && $route['args']) {
			$route['argv'][0] = '{{**}}' . $route['argv'][0];
		}
	}

	public function afterController(Router &$router, Input &$input, Output &$output)
	{
		logMsg('Event: ' . __FUNCTION__);
		logMsg(print_r($router->getMatched(), true));
		logMsg($input->requestUri());
		logMsg($input->requestMethod());
		logMsg($output->getOutput());

		$route = $router->getMatched();

		if ($route['requestURI'] == '/test' && $route['requestMethod'] == 'GET') {
			$html = $output->getOutput();

			$html = str_replace('{{**}}', 'It\'s just bikes: ', $html);

			$output->setOutput($html);
		}
	}

	public function afterOutput(Router &$router, Input &$input, Output &$output)
	{
		logMsg('Event: ' . __FUNCTION__);
		logMsg(print_r($router->getMatched(), true));
		logMsg($input->requestUri());
		logMsg($input->requestMethod());
	}
} /* end class */
