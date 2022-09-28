<?php

declare(strict_types=1);

namespace app\controllers;

use dmyers\disc\disc;
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

	public function disc()
	{
		disc::root(__ROOT__);

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

		$array = [
			'section1' => [
				'name' => 'frank',
				'age' => 24,
			],
			'section2' => [
				'name' => 'pete',
				'age' => 28,
			]
		];

		$csv = array(
			array('name' => 'Don Stein', 'age' => '23',),
			array('name' => 'John Doe', 'age' => '21',),
			array('name' => 'Jen White', 'age' => '27',)
		);

		disc::file('/testing/test.ini')->export->ini($array);

		$ini = disc::file('/testing/test.ini')->import->ini();

		//d($ini);

		disc::file('/testing/test.csv')->export->csv($csv);

		$csv2 = disc::file('/testing/test.csv')->import->csv();

		$file = disc::file('/testing/newfile.txt')->create();
		$file->writeLine('Hello World');
		$file->write('Hello World on ' . date('M-d-Y'));
		$file->changePermissions(0777);

		disc::file('/testing/newfile.txt')->append()->writeLine('second line');

		//d($file->info());
		//d($file);

		$content = disc::file('/testing/newfile.txt')->import->content();

		//d($content);

		$dir = disc::directory('/testing');

		d('directory permissions ' . disc::directory('/testing')->permissions(disc::ALL));
		d('file permissions ' . disc::file('/testing/newfile.txt')->permissions(disc::PERMISSION));

		disc::directory('/dummy')->remove();
		disc::directory('/foo')->remove();
		disc::directory('/bar')->remove();
		disc::directory('/testing/foo')->remove();

		$dirCopy = $dir->copy('/dummy');

		d($dirCopy);

		$dirCopy->rename('bar');

		d($dirCopy);

		$dirCopy->rename('foo');

		d($dirCopy);

		$dirCopy->move('/testing/foo');

		d($dirCopy);

		disc::directory('/testing/foo')->remove();

		exit(0);
	}

	public function foo()
	{
		/* singleton vs factory */
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

		/* variable as a service */
		$html .= '<p>This is a test = ' . $c->{'$test'} . '<p>';

		$html .= '<p>' . env('DEBUG') . '</p>';
		$html .= '<p>' . env('ENVIRONMENT') . '</p>';

		$html .= '<p>' . $c->router->getUrl('product', ['abc', 123]) . '</p>';
		$html .= '<p>' . $c->router->getUrl('product', ['xyz', 890]) . '</p>';
		$html .= '<p>' . $c->router->getUrl('test', ['abc', 123]) . '</p>';
		$html .= '<p>' . $c->router->getUrl('home') . '</p>';
		$html .= '<p>' . $c->router->getUrl('assets') . '</p>';

		$html .= print_r(($c->events->events()), true);

		$c->router->redirect('product', ['abc', 123], 302);

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
