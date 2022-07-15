<?php

declare(strict_types=1);

namespace dmyers\orange;

use dmyers\orange\exceptions\ConfigNotFound;
use dmyers\orange\exceptions\ConfigFolderNotFound;

class Config
{
	protected $container = [];

	public function __construct(string $path)
	{
		if (!is_dir($path)) {
			throw new ConfigFolderNotFound($path);
		}

		foreach (glob($path . '/*.php') as $file) {
			$this->__set(basename($file, '.php'), require $file);
		}
	}

	public function __set($name, $value)
	{
		$this->container[strtolower($name)] = $value;
	}

	public function __get($name)
	{
		if (!$this->__isset($name)) {
			throw new ConfigNotFound($name);
		}

		return $this->container[strtolower($name)];
	}

	public function __isset($name)
	{
		return isset($this->container[strtolower($name)]);
	}

	public function __unset($name)
	{
		unset($this->container[strtolower($name)]);
	}
} /* end class */
