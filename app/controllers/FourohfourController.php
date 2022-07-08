<?php

declare(strict_types=1);

use dmyers\orange\Controller;

class FourohfourController extends Controller
{
	public function index()
	{
		$this->output->responseCode(404);

		return '404';
	}
} /* end class */