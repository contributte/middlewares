<?php

/**
 * Test: Application\MiddlewareApplication
 */

use Contributte\Middlewares\Application\MiddlewareApplication;
use Contributte\Psr7\Psr7Request;
use Contributte\Psr7\Psr7Response;
use Ninjify\Nunjuck\Notes;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

test(function () {
	$callback = function (Psr7Request $request, Psr7Response $response) {
		Notes::add('touched');
		return $response;
	};

	$app = new MiddlewareApplication($callback);
	$app->run();

	Assert::equal(['touched'], Notes::fetch());
});
