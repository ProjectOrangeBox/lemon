<?php

declare(strict_types=1);

return [
	'case' => 'lower', /* upper or lower if let empty this will default to the PHP default*/
	'raw' => file_get_contents('php://input'),
	'post' => $_POST,
	'get' => $_GET,
	'request' => $_REQUEST,
	'server' => $_SERVER,
	'env' => $_ENV,
	'cookie' => $_COOKIE,
];
