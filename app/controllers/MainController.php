<?php

declare(strict_types=1);

namespace app\controllers;

use dmyers\orange\Controller;

class MainController extends Controller
{
	public function index()
	{	
		$appConfig = container()->config->app;

		$data = [
			'name' => $appConfig['name'],
			'version' => $appConfig['version'],
			'siteUrl' => siteUrl(),
		];

		return $this->output->view('/index', $data);
	}
} /* end class */