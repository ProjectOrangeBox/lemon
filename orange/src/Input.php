<?php

declare(strict_types=1);

namespace dmyers\orange;

class Input
{
	protected $input = [];

	public function __construct(array $config)
	{
		$this->input['raw'] = $config['raw'];
		$this->input['post'] = array_change_key_case($config['post'], CASE_LOWER);
		$this->input['get'] = array_change_key_case($config['get'], CASE_LOWER);
		$this->input['request'] = array_change_key_case($config['request'], CASE_LOWER);
		$this->input['server'] = array_change_key_case($config['server'], CASE_LOWER);
	}

	public function raw()
	{
		return $this->_pick('raw');
	}

	public function post(string $name = null, $default = null)
	{
		return $this->_pick('post', $name, $default);
	}

	public function get(string $name = null, $default = null)
	{
		return $this->_pick('get', $name, $default);
	}

	public function request(string $name = null, $default = null)
	{
		return $this->_pick('request', $name, $default);
	}

	public function server(string $name = null, $default = null)
	{
		return $this->_pick('server', $name, $default);
	}

	protected function _pick(string $type, ?string $name = null, $default = null)
	{
		if ($name === null) {
			$value = $this->input[$type];
		} elseif (isset($this->input[$type][strtolower($name)])) {
			$value = $this->input[$type][strtolower($name)];
		} else {
			$value = $default;
		}

		return $value;
	}
} /* end class */
