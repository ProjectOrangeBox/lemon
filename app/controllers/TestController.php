<?php

declare(strict_types=1);

use dmyers\orange\Event;
use dmyers\orange\Controller;

class TestController extends Controller
{
	public function index($arg1, $id)
	{
		return $this->output->view('/test', ['arg1' => $arg1, 'id' => $id]);
	}
} /* end class */
