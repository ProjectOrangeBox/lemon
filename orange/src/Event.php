<?php

declare(strict_types=1);

namespace dmyers\orange;

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
	protected $listeners = [];

	public function __construct(array $config)
	{
		foreach ($config as $name => $events) {
			foreach ($events as $options) {
				if ($this->is_closure($options[0])) {
					$priority = isset($options[1]) ? $options[1] : self::PRIORITY_NORMAL;

					$this->register($name, $options[0], $priority);
				} else {
					$priority = isset($options[3]) ? $options[3] : self::PRIORITY_NORMAL;

					$this->register($name, function (&...$arguments) use ($options) {
						$className = $options[0];
						$classMethod = $options[1];

						return (new $className)->$classMethod(...$arguments);
					}, $priority);
				}
			}
		}
	}

	/**
	 * Register a listener
	 *
	 * #### Example
	 * ```php
	 * register('open.page',function(&$var1) { echo "hello $var1"; },EVENT::PRIORITY_HIGH);
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
	public function register(string $name, $callable, int $priority = self::PRIORITY_NORMAL): self
	{
		/* if they pass in a array treat it as a name=>closure pair */
		if (is_array($name)) {
			foreach ($name as $n) {
				$this->register($n, $callable, $priority);
			}
			return $this;
		}

		/* clean up the name */
		$this->normalizeName($name);

		$this->listeners[$name][self::SORTED] = !isset($this->listeners[$name]); // Sorted?
		$this->listeners[$name][self::PRIORITY][] = $priority;
		$this->listeners[$name][self::CALLABLE][] = $callable;

		/* allow chaining */
		return $this;
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
		$this->normalizeName($name);

		/* do we even have any events with this name? */
		if (isset($this->listeners[$name])) {
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
		$this->normalizeName($name);

		return isset($this->listeners[$name]);
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
		return array_keys($this->listeners);
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
		$this->normalizeName($name);

		return (isset($this->listeners[$name])) ? count($this->listeners[$name][self::PRIORITY]) : 0;
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
		$this->normalizeName($name);

		$removed = false;

		if (isset($this->listeners[$name])) {
			if ($matches == null) {
				unset($this->listeners[$name]);

				$removed = true;
			} else {
				foreach ($this->listeners[$name][self::CALLABLE] as $index => $check) {
					if ($check === $matches) {
						unset($this->listeners[$name][self::PRIORITY][$index]);
						unset($this->listeners[$name][self::CALLABLE][$index]);

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
		$this->listeners = [];

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
	protected function normalizeName(string &$name): void
	{
		$name = strtolower($name);
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
		$this->normalizeName($name);

		$sorted = [];

		if (isset($this->listeners[$name])) {
			/* The list is not sorted */
			if (!$this->listeners[$name][self::SORTED]) {
				/* Sort it! */
				array_multisort($this->listeners[$name][self::PRIORITY], SORT_DESC, SORT_NUMERIC, $this->listeners[$name][self::CALLABLE]);

				/* Mark it as sorted already! */
				$this->listeners[$name][self::SORTED] = true;
			}

			$sorted = $this->listeners[$name][self::CALLABLE];
		}

		return $sorted;
	}

	protected function is_closure($t)
	{
		return $t instanceof \Closure;
	}
} /* end class */
