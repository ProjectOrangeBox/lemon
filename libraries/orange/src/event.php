<?php

declare(strict_types=1);

namespace dmyers\orange;

use dmyers\orange\exceptions\eventCallableInvalid;

class Event
{
	const PRIORITY_LOWEST = 10;
	const PRIORITY_LOW = 20;
	const PRIORITY_NORMAL = 50;
	const PRIORITY_HIGH = 80;
	const PRIORITY_HIGHEST = 90;

	const SORTED = 0;
	const PRIORITY = 1;
	const CALLABLE = 2;

	/**
	 * storage for events
	 *
	 * @var array
	 */
	protected $events = [];

	public function __construct(array $config)
	{
		foreach ($config as $name => $events) {
			foreach ($events as $options) {
				/* option[0] is either a Closure or a string containing the class name and method seperated by :: (double colons) */
				$this->registerEvent($name, $options[0], $options[1] ?? self::PRIORITY_NORMAL);
			}
		}
	}

	/**
	 * Register a listener
	 *
	 * #### Example
	 * ```php
	 * register('open.page',function(&$var1) { echo "hello $var1"; },EVENT::PRIORITY_HIGH);
	 * register
	 * ```
	 * @access public
	 *
	 * @param string $name name of the event we want to listen for
	 * @param callable $callable function to call if the event if triggered
	 * @param int $priority the priority this listener has against other listeners
	 *
	 * @return Event
	 *
	 */
	public function register($name, $callable, int $priority = self::PRIORITY_NORMAL): self
	{
		/* if they pass in a array treat it as a name=>closure pair */
		if (is_array($name)) {
			foreach ($name as $n) {
				$this->registerEvent($n, $callable, $priority);
			}
		} else {
			$this->registerEvent($name, $callable, $priority);
		}

		return $this;
	}

	protected function registerEvent(string $name, $callable, int $priority): void
	{
		if ($this->isClosure($callable)) {
			/*
			register a closure
			
			function(&$var) {
				$var = 'Hello ' . $var. ' how are you?';
			}
			*/
			$this->registerClosureEvent($name, $callable, $priority);
		} elseif (is_string($callable)) {
			/*
			register a class & method
			
			[\app\libraries\Middleware::class.'::before']
			*/
			$this->registerClosureEvent($name, function (&...$arguments) use ($callable) {
				if (count(explode('::', $callable)) != 2) {
					throw new eventCallableInvalid($callable);
				}

				list($className, $classMethod) = explode('::', $callable, 2);

				return (new $className)->$classMethod(...$arguments);
			}, $priority);
		} else {
			throw new eventCallableInvalid();
		}
	}

	protected function registerClosureEvent(string $name, $callable, int $priority): void
	{
		/* clean up the name */
		$name = $this->normalizeName($name);

		$this->events[$name][self::SORTED] = !isset($this->events[$name]); // Sorted?
		$this->events[$name][self::PRIORITY][] = $priority;
		$this->events[$name][self::CALLABLE][] = $callable;
	}

	/**
	 * Trigger an event
	 *
	 * #### Example
	 * ```php
	 * trigger('open.page',$var1);
	 * ```
	 * @param string $name event to trigger
	 * @param mixed ...$arguments pass by reference
	 *
	 * @return Event
	 *
	 * @access public
	 *
	 */
	public function trigger(string $name, &...$arguments): self
	{
		/* clean up the name */
		$name = $this->normalizeName($name);

		/* do we even have any events with this name? */
		if (isset($this->events[$name])) {
			foreach ($this->listeners($name) as $listener) {
				/* stop processing on return of false */
				if ($listener(...$arguments) === false) {
					break;
				}
			}
		}

		/* allow chaining */
		return $this;
	}

	/**
	 *
	 * Is there any listeners for a certain event?
	 *
	 * #### Example
	 * ```php
	 * $bool = ci('event')->has('page.load');
	 * ```
	 * @access public
	 *
	 * @param string $name event to search for
	 *
	 * @return bool
	 *
	 */
	public function has(string $name): bool
	{
		/* clean up the name */
		$name = $this->normalizeName($name);

		return isset($this->events[$name]);
	}

	/**
	 *
	 * Return an array of all of the event names
	 *
	 * #### Example
	 * ```php
	 * $triggers = ci('event')->events();
	 * ```
	 * @access public
	 *
	 * @return array
	 *
	 */
	public function events(): array
	{
		return array_keys($this->events);
	}

	/**
	 *
	 * Return the number of events for a certain name
	 *
	 * #### Example
	 * ```php
	 * $listeners = ci('event')->count('database.user_model');
	 * ```
	 * @access public
	 *
	 * @param string $name
	 *
	 * @return int
	 *
	 */
	public function count(string $name): int
	{
		/* clean up the name */
		$name = $this->normalizeName($name);

		return (isset($this->events[$name])) ? count($this->events[$name][self::PRIORITY]) : 0;
	}

	/**
	 *
	 * Removes a single listener from an event.
	 * this doesn't work for closures!
	 *
	 * @access public
	 *
	 * @param string $name
	 * @param $matches
	 *
	 * @return bool
	 *
	 */
	public function unregister(string $name, $matches = null): bool
	{
		/* clean up the name */
		$name = $this->normalizeName($name);

		$removed = false;

		if (isset($this->events[$name])) {
			if ($matches == null) {
				unset($this->events[$name]);

				$removed = true;
			} else {
				foreach ($this->events[$name][self::CALLABLE] as $index => $check) {
					if ($check === $matches) {
						unset($this->events[$name][self::PRIORITY][$index]);
						unset($this->events[$name][self::CALLABLE][$index]);

						$removed = true;
					}
				}
			}
		}

		return $removed;
	}

	/**
	 *
	 * Removes all listeners.
	 *
	 * If the event_name is specified, only listeners for that event will be
	 * removed, otherwise all listeners for all events are removed.
	 *
	 * @access public
	 *
	 * @param string $name
	 *
	 * @return \Event
	 *
	 */
	public function unregisterAll(): self
	{
		$this->events = [];

		/* allow chaining */
		return $this;
	}

	/**
	 *
	 * Normalize the event name
	 *
	 * @access protected
	 *
	 * @param string $name
	 *
	 * @return void
	 *
	 */
	protected function normalizeName(string $name): string
	{
		return mb_convert_case($name, MB_CASE_LOWER, mb_detect_encoding($name));
	}

	/**
	 *
	 * Do the actual sorting
	 *
	 * @access protected
	 *
	 * @param string $name
	 *
	 * @return array
	 *
	 */
	protected function listeners(string $name): array
	{
		$name = $this->normalizeName($name);

		$sorted = [];

		if (isset($this->events[$name])) {
			/* The list is not sorted */
			if (!$this->events[$name][self::SORTED]) {
				/* Sort it! */
				array_multisort($this->events[$name][self::PRIORITY], SORT_DESC, SORT_NUMERIC, $this->events[$name][self::CALLABLE]);

				/* Mark it as sorted already! */
				$this->events[$name][self::SORTED] = true;
			}

			$sorted = $this->events[$name][self::CALLABLE];
		}

		return $sorted;
	}

	protected function isClosure($t)
	{
		return $t instanceof \Closure;
	}
} /* end class */
