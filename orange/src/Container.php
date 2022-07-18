<?php

declare(strict_types=1);

namespace dmyers\orange;

use dmyers\orange\exceptions\ServiceNotFound;

class Container
{
	/**
	 * Registered Services
	 *
	 * @var array
	 */
	protected static $registeredServices = [];

	public function __construct(array $serviceArray = null)
	{
		if (is_array($serviceArray)) {
			foreach ($serviceArray as $serviceName => $options) {
				if (is_array($options)) {
					$this->register($serviceName, $options[0], false);
				} else {
					$this->register($serviceName, $options, true);
				}
			}
		}
	}

	/**
	 * __get
	 *
	 * see get(...)
	 *
	 * @param mixed $serviceName
	 * @return mixed
	 */
	public function __get($serviceName)
	{
		return $this->get($serviceName);
	}

	/**
	 * __isset
	 *
	 * see has(...)
	 *
	 * @param mixed $serviceName
	 * @return bool
	 */
	public function __isset($serviceName): bool
	{
		return $this->has($serviceName);
	}

	public function __unset($serviceName)
	{
		unset(self::$registeredServices[strtolower($serviceName)]);
	}

	/**
	 * Get a PHP object by service name
	 *
	 * @param string $serviceName
	 * @return mixed
	 */
	public function get(string $serviceName)
	{
		$serviceName = strtolower($serviceName);

		/* Is this service even registered? */
		if (!isset(self::$registeredServices[$serviceName])) {
			/* fatal */
			throw new ServiceNotFound($serviceName);
		}

		/* Is this a singleton or factory? */
		return (self::$registeredServices[$serviceName]['singleton']) ? self::singleton($serviceName) : self::factory($serviceName);
	}

	/**
	 * Check whether the Service been registered
	 *
	 * @param string $serviceName
	 * @return bool
	 */
	public function has(string $serviceName): bool
	{
		return isset(self::$registeredServices[strtolower($serviceName)]);
	}


	/**
	 * Register a new service as a singleton or factory
	 *
	 * @param string $serviceName Service Name
	 * @param closure $closure closure to call in order to instancate it.
	 * @param bool $singleton should this be a singleton or factory
	 * @return void
	 */
	public function register(string $serviceName, \Closure $closure, bool $singleton = false): void
	{
		self::$registeredServices[strtolower($serviceName)] = ['closure' => $closure, 'singleton' => $singleton, 'reference' => null];
	}

	public function reference(string $serviceName, $reference, bool $singleton = true): void
	{
		self::$registeredServices[strtolower($serviceName)] = ['closure' => null, 'singleton' => $singleton, 'reference' => $reference];
	}

	/**
	 * Get the same instance of a service
	 *
	 * @param string $serviceName
	 * @return mixed
	 */
	protected function singleton(string $serviceName)
	{
		if (!isset(self::$registeredServices[$serviceName]['reference'])) {
			self::$registeredServices[$serviceName]['reference'] = self::factory($serviceName);
		}

		return self::$registeredServices[$serviceName]['reference'];
	}

	/**
	 * Get new instance of a service
	 *
	 * @param string $serviceName
	 * @return mixed
	 */
	protected function factory(string $serviceName)
	{
		return self::$registeredServices[$serviceName]['closure']($this);
	}

	/**
	 * returns a debug array
	 *
	 * @return array
	 */
	public function debug(): array
	{
		$debug = [];

		foreach (self::$registeredServices as $key => $record) {
			$debug[$key] = ['singleton' => $record['singleton'], 'attached' => isset(self::$registeredServices[$key]['reference'])];
		}

		return $debug;
	}
} /* end class */
