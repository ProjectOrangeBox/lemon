<?php

declare(strict_types=1);

namespace app\controllers;

use dmyers\orange\Container;
use dmyers\orange\Controller;

class TestController extends Controller
{
	public function index($arg1, $id)
	{
		logMsg('this is a test', 'EMERGENCY');
		logMsg('foobar', 'CRITICAL');

		return $this->output->view('/test', ['arg1' => $arg1, 'id' => $id]);
	}

	public function foo()
	{
		$c = new Container;

		/* factory */
		$f1 = $c->foo;
		$f2 = $c->foo;

		$f1->set('name', 'Don');
		$f2->set('name', 'Jen');

		/* singleton */
		$f3 = $c->bar;
		$f3->set('name', 'Doug');

		$f4 = $c->bar;
		$f4->set('name', 'Peter');

		return $f1->get('name') . ' / ' . $f2->get('name') . ' / ' . $f3->get('name') . ' / ' . $f4->get('name');
	}
} /* end class */
