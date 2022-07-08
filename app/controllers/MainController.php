<?php

declare(strict_types=1);

use dmyers\orange\Controller;

class MainController extends Controller
{
	public function index()
	{
		return view('/index');
	}
} /* end class */