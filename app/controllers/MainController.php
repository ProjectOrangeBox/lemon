<?php

declare(strict_types=1);

namespace app\controllers;

use dmyers\orange\Controller;

class MainController extends Controller
{
	public function index()
	{
		$data['name'] = $this->input->env('name');
		$data['version'] = $this->input->env('version');

		return $this->output->view('/index', $data);
	}
} /* end class */