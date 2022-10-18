<?php

declare(strict_types=1);

namespace app\libraries;

use dmyers\orange\Container;

class Middleware
{
	public function before(Container &$container)
	{
		$route = $container->router->route();

		if (substr($route['requestURI'], 0, 5) == '/test' && $route['requestMethod'] == 'GET' && $route['args']) {
			$route['argv'][0] = '{{**}}' . $route['argv'][0];
		}
	}

	public function after(Container &$container)
	{
		$route = $container->router->route();

		if ($route['requestURI'] == '/test' && $route['requestMethod'] == 'GET') {
			$html = $container->output->getOutput();

			$html = str_replace('{{**}}', 'It\'s just bikes: ', $html);

			$container->output->setOutput($html);
		}
	}
} /* end class */
