<?php

declare(strict_types=1);

use dmyers\disc\disc;

if (!defined('__ROOT__')) {
	define('__ROOT__', realpath(__DIR__ . '/support'));
}

require __DIR__ . '/../vendor/autoload.php';

disc::root(__ROOT__);

$file = disc::file('/test.txt')->create();

$file->writeLine('This is a test');
$file->close();


$file = disc::file('/test.txt')->open();
$chars = $file->characters('abc');

d($chars);

//$contents = $file->contents();

//d($contents);

echo $file->size() . chr(10);

d($file->asArray());

d($file->directory());

disc::file('/test.txt')->remove();
disc::directory('/')->removeContents();
