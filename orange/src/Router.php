<?php

declare(strict_types=1);

namespace dmyers\orange;

use dmyers\orange\exceptions\RouteNotFound;

class Router
{
	const CONTROLLER = 0;
	const METHOD = 1;

	protected $routes = null;
	protected $input = null;
	protected $responds = [];

	public function __construct(array $routes)
	{
		$this->routes = $routes;
	}

	public function responds(string $match = null) /* mixed string|array */
	{
		return (isset($this->responds[$match])) ? $this->responds[$match] : $this->responds;
	}

	public function route(string $requestUri, string $requestMethod): array
	{
		$url = false;
		$requestMethod = strtoupper($requestMethod);

		foreach ($this->routes as $route) {
			$matchedMethod = strtoupper($route['method']);

			/* check if the current request matches the expression */
			if (($requestMethod == $matchedMethod || $route['method'] == '*') && preg_match("@^" . $route['url'] . "$@D", '/' . trim($requestUri, '/'), $args)) {
				/* remove the first arg */
				$url = array_shift($args);

				/* pop out of foreach loop */
				break;
			}
		}

		if (!$url) {
			throw new RouteNotFound();
		}

		$this->responds = [
			'requestMethod' => $requestMethod,
			'requestURI' => $requestUri,
			'matchedURI' => $route['url'],
			'matchedMethod' => $matchedMethod,
			'controller' => $route['callback'][self::CONTROLLER],
			'method' => $route['callback'][self::METHOD],
			'url' => $url,
			'args' => $args,
			'count' => count($args),
			'has' => (bool)count($args),
		];

		return $this->responds;
	}
} /* end class */