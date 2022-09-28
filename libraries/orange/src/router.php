<?php

declare(strict_types=1);

namespace dmyers\orange;

use dmyers\orange\exceptions\InvalidValue;
use dmyers\orange\exceptions\RouteNotFound;
use dmyers\orange\exceptions\RouterNameNotFound;

class Router
{
	const CONTROLLER = 0;
	const METHOD = 1;

	protected $routes = null;
	protected $input = null;
	protected $route = [];

	public function __construct(array $routes)
	{
		$this->routes = $routes;
	}

	public function route(string $match = null) /* mixed string|array */
	{
		return (isset($this->route[$match])) ? $this->route[$match] : $this->route;
	}

	public function match(string $requestUri, string $requestMethod): array
	{
		$url = false;
		$requestMethod = strtoupper($requestMethod);

		foreach ($this->routes as $route) {
			if (isset($route['method'])) {
				$matchedMethod = strtoupper($route['method']);

				/* check if the current request matches the expression */
				if (($requestMethod == $matchedMethod || $route['method'] == '*') && preg_match("@^" . $route['url'] . "$@D", '/' . trim($requestUri, '/'), $args)) {
					/* remove the first arg */
					$url = array_shift($args);

					/* pop out of foreach loop */
					break;
				}
			}
		}

		if (!$url) {
			throw new RouteNotFound();
		}

		$this->route = [
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

		return $this->route;
	}

	public function getUrl(string $name, array $arguments = []): string
	{
		$url = '';
		$name = $this->normalizeName($name);
		$argumentsCount = count($arguments);

		foreach ($this->routes as $route) {
			if (isset($route['name']) && $this->normalizeName($route['name']) == $name) {
				if (!isset($route['url'])) {
					throw new InvalidValue('Missing url value for "' . $name . '"');
				}

				$url = $route['url'];

				preg_match_all('/\((.*?)\)/m', $url, $matches, PREG_SET_ORDER, 0);

				$matchesCount = count($matches);

				if ($argumentsCount != $matchesCount) {
					throw new InvalidValue('Parameter count mismatch. Expecting ' . $matchesCount . ' got ' . $argumentsCount);
				}

				foreach ($matches as $index => $match) {
					$value = (string)$arguments[$index];

					if (!preg_match('@' . $match[0] . '@m', $value)) {
						throw new InvalidValue('Parameter mismatch. Expecting ' . $match[1] . ' got ' . $value);
					}

					$url = str_replace($match[0], $value, $url);
				}

				break;
			}
		}

		if (empty($url)) {
			throw new RouterNameNotFound('Path "' . $name . '" not found');
		}

		return $url;
	}

	public function redirect(string $name, array $arguments, int $responseCode = 0)
	{
		$host = '';

		/* find host name if they set it */
		foreach ($this->routes as $route) {
			if (isset($route['host'])) {
				$host = $route['host'];
			}
		}

		header('Location: ' . $host . $this->getUrl($name, $arguments), true, $responseCode);

		exit(0);
	}

	protected function normalizeName(string $name): string
	{
		return mb_convert_case($name, MB_CASE_LOWER, mb_detect_encoding($name));
	}
} /* end class */
