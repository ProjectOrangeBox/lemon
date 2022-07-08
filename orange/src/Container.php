<?php

declare(strict_types=1);

namespace dmyers\orange;

use dmyers\orange\exceptions\ContainerItemNotFound;

class Container
{
	protected $storage = [];

	public function set(string $name, $value)
	{
		$this->storage[$name] = $value;
	}

	public function get(string $name)
	{
		if (!isset($this->storage[$name])) {
			throw new ContainerItemNotFound($name);
		}

		return $this->storage[$name];
	}

	public function __set(string $name, $value)
	{
		$this->set($name, $value);
	}

	public function __get(string $name)
	{
		$this->get($name);
	}

	public function has(string $name): bool
	{
		return isset($this->storage[$name]);
	}
} /* end class */
