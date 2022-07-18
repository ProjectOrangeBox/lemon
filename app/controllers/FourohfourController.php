<?php

declare(strict_types=1);

namespace app\controllers;

use dmyers\orange\Controller;

class FourohfourController extends Controller
{
	public function index()
	{
		return $this->output->responseCode(404)->view('/404');
	}
} /* end class */