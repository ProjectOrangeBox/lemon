<?php

declare(strict_types=1);

use dmyers\disc\disc;

define('__ROOT__', realpath(__DIR__ . '/../'));

require __DIR__ . '/../vendor/autoload.php';

cli();

function main($container)
{
	disc::root(realpath(__DIR__ . '/support'));

	$file = disc::file('/test.txt')->create();

	d('before ' . $file->size());

	$file->writeLine('This is a test');
	$file->close();

	d('after ' . $file->size());
	d('after ' . filesize(__DIR__ . '/support/test.txt'));

	$file = disc::file('/test.txt')->open();


	$chars = $file->characters(1);

	d($chars);

	//$contents = $file->contents();

	//d($contents);

	echo $file->size() . chr(10);

	d($file->asArray());

	d($file->directory());

	disc::file('/test.txt')->remove();
	disc::directory('/')->removeContents();
}
