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
		$this->set($name, $value);
	}

	public function set(string $name, $value): void
	{
		$this->container[$this->normalizeName($name)] = $value;
	}

	public function __get($name)
	{
		return $this->get($name);
	}

	public function get(string $name)
	{
		$name = $this->normalizeName($name);

		if (!$this->__isset($name)) {
			throw new ConfigNotFound($name);
		}

		return $this->container[$name];
	}

	public function __isset($name)
	{
		return $this->isset($name);
	}

	public function isset(string $name): bool
	{
		return isset($this->container[$this->normalizeName($name)]);
	}

	public function __unset($name)
	{
		$this->unset($name);
	}

	public function unset(string $name)
	{
		unset($this->container[$this->normalizeName($name)]);
	}

	public function __toString()
	{
		return json_encode($this->container, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
	}

	public function __debugInfo()
	{
		return $this->container;
	}

	protected function normalizeName(string $name): string
	{
		return mb_convert_case($name, MB_CASE_LOWER, mb_detect_encoding($name));
	}
} /* end class */
