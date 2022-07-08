<?php

declare(strict_types=1);

namespace dmyers\orange;

class Controller
{
	protected $output = null;
	protected $input = null;
	protected $config = [];

	public function __construct(input $input, output $output)
	{
		$this->input = $input;
		$this->output = $output;
		$this->config = container()->config;
	}
} /* end class */
