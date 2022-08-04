<?php

declare(strict_types=1);

namespace app\controllers;

use dmyers\orange\Container;
use dmyers\orange\disc\Disc;
use dmyers\orange\Controller;

class TestController extends Controller
{
	public function index($arg1, $id)
	{
		logMsg('this is a test', 'EMERGENCY');
		logMsg('foobar', 'CRITICAL');

		return $this->output->view('/test', ['arg1' => $arg1, 'id' => $id]);
	}

	public function disc()
	{
		Disc::root(__ROOT__);

		Disc::save('/foobar/new.ini', [
			'section1' => [
				'name' => 'frank',
				'age' => 24,
			],
			'section2' => [
				'name' => 'pete',
				'age' => 28,
			]
		]);

		$ini = Disc::load('/foobar/new.ini');

		d($ini);

		exit(0);
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

		$html .= '<p>' . env('DEBUG') . '</p>';
		$html .= '<p>' . env('ENVIRONMENT') . '</p>';

		$html .= '<p>' . $c->router->getUrl('product', 'abc', 123) . '</p>';
		$html .= '<p>' . $c->router->getUrl('product', 'xyz', 890) . '</p>';
		$html .= '<p>' . $c->router->getUrl('test', 'abc', 123) . '</p>';
		$html .= '<p>' . $c->router->getUrl('home') . '</p>';
		$html .= '<p>' . $c->router->getUrl('assets') . '</p>';

		return $html;
	}

	public function bar()
	{
		$obj = new \StdClass;

		$obj->name = "Don Myers";
		$obj->age = 21;

		$pet1 = new \StdClass;
		$pet1->name = "Balley";
		$pet1->age = 4;
		$pet1->type = 'dog';

		$pet2 = new \StdClass;
		$pet2->name = "Manchester";
		$pet2->age = 2;
		$pet2->type = 'dog';

		$obj->pets = [$pet1, $pet2];

		return $this->output->view('json', ['json' => $obj]);
	}
} /* end class */
