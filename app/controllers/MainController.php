<?php

declare(strict_types=1);

use dmyers\orange\Controller;

class MainController extends Controller
{
	public function index()
	{
		return $this->output->view('/index');
	}
} /* end class */