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
			foreach ($serviceArray as $serviceName => $option) {
				$this->__set($serviceName, $option);
			}
		}
	}

	/**
	 * Method __get
	 * 
	 * $foo = $container->{'$var'};
	 * $foo = $container->logger;
	 *
	 * @param string $serviceName Service Name
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
		return (self::$registeredServices[$serviceName]['singleton']) ? $this->singleton($serviceName) : $this->factory($serviceName);
	}

	/**
	 * Method __set
	 * 
	 * $container->{'$var'} = 'foobar;
	 * $container->logger = function(){};
	 * $container->{'factory[]'} = function(){};
	 * 
	 *
	 * @param string $serviceName Service Name
	 * @param $reference $reference [explicite description]
	 *
	 * @return void
	 */
	public function __set(string $serviceName, $option): void
	{
		$singleton = true;

		if (substr($serviceName, -2) == '[]') {
			$serviceName = substr($serviceName, 0, -2);
			$singleton = false;
		}

		if ($option instanceof \Closure) {
			$closure = $option;
			$reference = null;
		} else {
			$closure = null;
			$reference = $option;
		}

		self::$registeredServices[strtolower($serviceName)] = [
			'closure' => $closure,
			'singleton' => $singleton,
			'reference' => $reference
		];
	}

	/**
	 * Method __isset
	 *
	 * Check whether the Service been registered
	 *
	 * @param string $serviceName Service Name
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
	 * Remove a service
	 *
	 * @param string $serviceName Service Name
	 *
	 * @return void
	 */
	public function __unset(string $serviceName): void
	{
		unset(self::$registeredServices[strtolower($serviceName)]);
	}

	/**
	 * Get the same instance of a service
	 *
	 * @param string $serviceName Service Name
	 * 
	 * @return mixed
	 */
	protected function singleton(string $serviceName)
	{
		if (!isset(self::$registeredServices[$serviceName]['reference'])) {
			self::$registeredServices[$serviceName]['reference'] = $this->factory($serviceName);
		}

		return self::$registeredServices[$serviceName]['reference'];
	}

	/**
	 * Get new instance of a service
	 *
	 * @param string $serviceName Service Name
	 * 
	 * @return mixed
	 */
	protected function factory(string $serviceName)
	{
		return self::$registeredServices[$serviceName]['closure']($this);
	}

	/**
	 * Return Debug Array
	 *
	 * @return array
	 */
	public function __debugInfo(): array
	{
		$debug = [];

		foreach (self::$registeredServices as $key => $record) {
			if (self::$registeredServices[$key]['closure'] instanceof \Closure) {
				$type = 'Service Generator';
			} else {
				$check = (isset(self::$registeredServices[$key]['reference'])) ? 'reference' : 'closure';
				$type = gettype(self::$registeredServices[$key][$check]);
			}

			$debug[$key] = [
				'singleton' => $record['singleton'],
				'attached' => isset(self::$registeredServices[$key]['reference']),
				'type' => $type,
			];
		}

		return $debug;
	}
} /* end class */
