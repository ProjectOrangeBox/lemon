<?php

declare(strict_types=1);

namespace app\controllers;

use dmyers\orange\Controller;

class RestController extends Controller
{
	public function main()
	{
		var_dump(container()->router->route());
	}
} /* end class */
