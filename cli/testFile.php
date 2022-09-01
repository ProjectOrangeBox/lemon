<?php

declare(strict_types=1);

define('__ROOT__', realpath(__DIR__ . '/../'));

require __ROOT__ . '/vendor/autoload.php';

$container = cli();

use dmyers\disc\disc;

disc::root(__ROOT__ . '/var/support');

$file = disc::file('/test.txt')->create();

d('before ' . $file->size());

$file->writeLine('This is a test');
$file->close();

d('after ' . $file->size());

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
