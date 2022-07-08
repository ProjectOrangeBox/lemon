<?php

declare(strict_types=1);

use dmyers\orange\Event;
use dmyers\orange\Controller;

class TestController extends Controller
{
	public function index($arg1, $id)
	{
		echo '<pre>Testing' . chr(10);

		$e = app('events');

		$e->register('email@test', function (&$arg1) {
			$arg1 = $arg1 . '[highest1]';
		}, Event::PRIORITY_HIGHEST);

		$e->register('email@test', function (&$arg1) {
			$arg1 = $arg1 . '[low]';
		}, Event::PRIORITY_LOW);

		$e->register('email@test', function (&$arg1) {
			$arg1 = $arg1 . '[highest2]';
		}, Event::PRIORITY_HIGHEST);

		$e->register('email@test', function (&$arg1) {
			$arg1 = $arg1 . '[normal]';
		}, Event::PRIORITY_NORMAL);

		$e->trigger('email@test', $arg1);

		echo $arg1 . $id;

		echo chr(10);

		var_dump($e->has('email@test'));

		var_dump($e->events());

		var_dump(app('router')->responds());
	}
} /* end class */
