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

	/**
	 * Method __construct
	 *
	 * @param array $serviceArray [array of services]
	 *
	 * @return void
	 */
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
	 * Method __get
	 * 
	 * $foo = $container->{'$var'};
	 * $foo = $container->logger;
	 *
	 * @param string $serviceName [explicite description]
	 *
	 * @return void
	 */
	public function __get(string $serviceName)
	{
		/* Is this service even registered? */
		if (!$this->__isset($serviceName)) {
			/* fatal */
			throw new ServiceNotFound($serviceName);
		}

		$serviceName = strtolower($serviceName);

		/* Is this a singleton or factory? */
		return (self::$registeredServices[$serviceName]['singleton']) ? self::singleton($serviceName) : self::factory($serviceName);
	}

	/**
	 * Method __set
	 * 
	 * $container->{'$var'} = 'foobar;
	 * $container->logger = new Loggie;
	 * 
	 *
	 * @param string $serviceName [explicite description]
	 * @param $reference $reference [explicite description]
	 *
	 * @return void
	 */
	public function __set(string $serviceName, $reference): void
	{
		self::$registeredServices[strtolower($serviceName)] = ['closure' => null, 'singleton' => true, 'reference' => $reference];
	}

	/**
	 * Method __isset
	 *
	 * Check whether the Service been registered
	 *
	 * @param string $serviceName [explicite description]
	 * 
	 * @return bool
	 */
	public function __isset(string $serviceName): bool
	{
		return isset(self::$registeredServices[strtolower($serviceName)]);
	}

	/**
	 * Method __unset
	 *
	 * @param string $serviceName [explicite description]
	 *
	 * @return void
	 */
	public function __unset(string $serviceName): void
	{
		unset(self::$registeredServices[strtolower($serviceName)]);
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
	public function __debugInfo(): array
	{
		$debug = [];

		foreach (self::$registeredServices as $key => $record) {
			$debug[$key] = ['singleton' => $record['singleton'], 'attached' => isset(self::$registeredServices[$key]['reference'])];
		}

		return $debug;
	}
} /* end class */
