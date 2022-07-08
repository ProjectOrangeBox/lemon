<?php

declare(strict_types=1);

namespace dmyers\orange;

class Container
{
	protected $storage = [];

	public function __set(string $name, $value)
	{
		$this->storage[$name] = $value;
	}

	public function __get(string $name)
	{
		return $this->storage[$name];
	}
} /* end class */
