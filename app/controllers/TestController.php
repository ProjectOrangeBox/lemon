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

		$html = '<pre>';
		$html .= '<p>Don = ' . $f1->get('name') . '</p>';
		$html .= '<p>Jen = ' . $f2->get('name') . '</p>';
		$html .= '<p>Peter = ' . $f3->get('name') . '</p>';
		$html .= '<p>Peter = ' . $f4->get('name') . '</p>';

		$html .= '<p>This is a test = ' . $c->{'$test'} . '<p>';

		var_dump($c);

		return $html;
	}
} /* end class */
