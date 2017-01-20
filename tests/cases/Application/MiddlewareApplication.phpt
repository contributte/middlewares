<?php

/**
 * Test: Application\MiddlewareApplication
 */

use Contributte\Middlewares\Application\MiddlewareApplication;
use Ninjify\Nunjuck\Notes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

test(function () {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response) {
		Notes::add('touched');

		return $response;
	};

	$app = new MiddlewareApplication($callback);
	$app->run();

	Assert::equal(['touched'], Notes::fetch());
});
