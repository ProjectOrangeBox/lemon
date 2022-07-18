<?php

declare(strict_types=1);

namespace app\libraries;

class Foo
{
	protected $data = [];

	public function set(string $name, $value)
	{
		$this->data[$name] = $value;
	}

	public function get(string $name)
	{
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}
} /* end class */