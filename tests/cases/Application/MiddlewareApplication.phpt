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

// Test invoking of callback
test(function () {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response) {
		Notes::add('touched');

		return $response;
	};

	$app = new MiddlewareApplication($callback);
	$app->run();

	Assert::equal(['touched'], Notes::fetch());
});

// Response text
test(function () {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response) {
		$response->getBody()->write('OK');

		return $response;
	};

	$app = new MiddlewareApplication($callback);
	ob_start();
	$app->run();
	Assert::equal('OK', ob_get_contents());
});

// Throws exception
test(function () {
	Assert::throws(function () {
		$callback = function (ServerRequestInterface $request, ResponseInterface $response) {
			return NULL;
		};

		$app = new MiddlewareApplication($callback);
		$app->run();
	}, RuntimeException::class);
});
