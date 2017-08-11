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

// Return invalid response
test(function () {
	Assert::throws(function () {
		$callback = function (ServerRequestInterface $request, ResponseInterface $response) {
			return NULL;
		};

		$app = new MiddlewareApplication($callback);
		$app->run();
	}, RuntimeException::class);
});

// Finalize
test(function () {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response) {
		return $response->withStatus(300);
	};

	$app = new MiddlewareApplication($callback);
	$response = $app->run();

	Assert::type(ResponseInterface::class, $response);
	Assert::equal(300, $response->getStatusCode());
});

// Send headers
test(function () {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
		$response = $next($request, $response);

		return $response->withHeader('X-Foo', 'bar');
	};

	$app = new MiddlewareApplication($callback);
	$response = $app->run();
	$headers = $response->getHeaders();
	Assert::equal(['X-Foo' => ['bar']], $headers);
});

// Throws exception
test(function () {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response) {
		throw new RuntimeException('Oh mama');
	};

	$app = new MiddlewareApplication($callback);
	$app->onError[] = function (MiddlewareApplication $app, Exception $e, ServerRequestInterface $req, ResponseInterface $res) {
		Notes::add('CALLED');
	};

	Assert::throws(function () use ($app) {
		$app->run();
	}, RuntimeException::class, 'Oh mama');
	Assert::equal(['CALLED'], Notes::fetch());
});

// Throws exception and catch
test(function () {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response) {
		throw new RuntimeException('Oh mama');
	};

	$app = new MiddlewareApplication($callback);
	$app->setCatchExceptions(TRUE);
	$app->onError[] = function (MiddlewareApplication $app, Exception $e, ServerRequestInterface $req, ResponseInterface $res) {
		Notes::add('CALLED');
	};

	$app->run();
	Assert::equal(['CALLED'], Notes::fetch());
});

// Throws exception and handle in onError
test(function () {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response) {
		throw new RuntimeException('Oh mama');
	};

	$app = new MiddlewareApplication($callback);
	$app->onError[] = function (MiddlewareApplication $app, Exception $e, ServerRequestInterface $req, ResponseInterface $res) {
		Notes::add('CALLED');

		return 'OK';
	};

	Assert::equal('OK', $app->run());
	Assert::equal(['CALLED'], Notes::fetch());
});

// Dispatching events
test(function () {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response) {
		$response->getBody()->write('OK');

		return $response;
	};

	$app = new MiddlewareApplication($callback);
	$app->onStartup[] = function (MiddlewareApplication $app) {
		Notes::add('STARTUP');
	};
	$app->onRequest[] = function (MiddlewareApplication $app, ServerRequestInterface $req, ResponseInterface $res) {
		Notes::add('REQUEST');
	};
	$app->onResponse[] = function (MiddlewareApplication $app, ServerRequestInterface $req, ResponseInterface $res) {
		Notes::add('RESPONSE');
	};

	Assert::equal('OK', (string) $app->run()->getBody());
	Assert::equal(['STARTUP', 'REQUEST', 'RESPONSE'], Notes::fetch());
});


// Dispatching events with return value as parameter
test(function () {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response) {
		$response->getBody()->write('OK');

		return $response;
	};

	$app = new MiddlewareApplication($callback);
	$app->onStartup[] = function (MiddlewareApplication $app) {
		Notes::add('STARTUP1');

		return '1';
	};
	$app->onStartup[] = function (MiddlewareApplication $app, $prev) {
		Notes::add('STARTUP2');
		Notes::add($prev);

		return '2';
	};
	$app->onStartup[] = function (MiddlewareApplication $app, $prev) {
		Notes::add('STARTUP3');
		Notes::add($prev);
	};

	Assert::equal('OK', (string) $app->run()->getBody());
	Assert::equal(['STARTUP1', 'STARTUP2', '1', 'STARTUP3', '2'], Notes::fetch());
});
