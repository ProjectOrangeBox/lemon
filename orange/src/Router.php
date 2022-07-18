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

	public function __construct(array $routes, input &$input)
	{
		$this->routes = $routes;
		$this->input = $input;
	}

	public function responds(string $match = null) /* mixed string|array */
	{
		return (isset($this->responds[$match])) ? $this->responds[$match] : $this->responds;
	}

	public function route(?string $request_uri = null, ?string $request_method = null): array
	{
		$requestMethod = ($request_method) ? $request_method : $this->input->server('request_method');
		$requestUri = ($request_uri) ? $request_uri : $this->input->server('request_uri');

		$url = false;

		foreach ($this->routes as $route) {
			/* check if the current request matches the expression */
			if ((strtoupper($requestMethod) == strtoupper($route['method']) || $route['method'] == '*') && preg_match("@^" . $route['url'] . "$@D", '/' . trim($requestUri, '/'), $args)) {
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
			'controller' => $route['callback'][self::CONTROLLER],
			'method' => $route['callback'][self::METHOD],
			'url' => $url,
			'args' => $args
		];

		return $this->responds;
	}
} /* end class */