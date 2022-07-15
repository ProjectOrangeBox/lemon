<?php

declare(strict_types=1);

namespace dmyers\orange;

use dmyers\orange\exceptions\ContainerItemNotFound;

class Container
{
	protected static $storage = [];

	public function __set(string $name, $value)
	{
		$this->set($name, $value);
	}

	public function set(string $name, $value)
	{
		self::$storage[$name] = $value;
	}

	public function __get(string $name)
	{
		return $this->get($name);
	}

	public function get(string $name)
	{
		if (!isset(self::$storage[$name])) {
			throw new ContainerItemNotFound($name);
		}

		return self::$storage[$name];
	}

	public function __unset(string $name): void
	{
		unset(self::$storage[$name]);
	}

	public function __isset(string $name): bool
	{
		return isset(self::$storage[$name]);
	}

	public function has(string $name): bool
	{
		return self::__isset($name);
	}
} /* end class */
