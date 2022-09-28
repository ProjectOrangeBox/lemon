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
			$this->__set(basename($file, '.php'), require_once $file);
		}
	}

	public function __set($name, $value)
	{
		$this->container[$this->normalizeName($name)] = $value;
	}

	public function __get($name)
	{
		$name = $this->normalizeName($name);

		if (!$this->__isset($name)) {
			throw new ConfigNotFound($name);
		}

		return $this->container[$name];
	}

	public function __isset($name)
	{
		return isset($this->container[$this->normalizeName($name)]);
	}

	public function __unset($name)
	{
		unset($this->container[$this->normalizeName($name)]);
	}

	protected function normalizeName(string $name): string
	{
		return mb_convert_case($name, MB_CASE_LOWER, mb_detect_encoding($name));
	}
} /* end class */
