<?php

declare(strict_types=1);

namespace app\controllers;

use dmyers\orange\Container;
use dmyers\orange\Controller;

class MainController extends Controller
{
	public function index()
	{
		$data['name'] = $this->input->env('name');
		$data['version'] = $this->input->env('version');

		$c = new Container;

		/* not a factory */
		$c->time = function () { // Register DB connection
			return microtime();
		};

		d($c->time);
		d($c->time);
		d($c->{'time[]'});

		return $this->output->view('/index', $data);
	}
} /* end class */