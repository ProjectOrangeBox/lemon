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

		if (file_exists(__ROOT__ . '/.env')) {
			$this->container = array_replace_recursive($this->container, parse_ini_file(__ROOT__ . '/.env', true, INI_SCANNER_TYPED));
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
