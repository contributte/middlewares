<?php declare(strict_types = 1);

use Contributte\Middlewares\Application\MiddlewareApplication;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\Notes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test invoking of callback
Toolkit::test(function (): void {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response) {
		Notes::add('touched');

		return $response;
	};

	$app = new MiddlewareApplication($callback);
	$app->run();

	Assert::equal(['touched'], Notes::fetch());
});

// Response text
Toolkit::test(function (): void {
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
Toolkit::test(function (): void {
	Assert::throws(function (): void {
		$callback = fn (ServerRequestInterface $request, ResponseInterface $response) => null;

		$app = new MiddlewareApplication($callback);
		$app->run();
	}, RuntimeException::class);
});

// Finalize
Toolkit::test(function (): void {
	$callback = fn (ServerRequestInterface $request, ResponseInterface $response) => $response->withStatus(300);

	$app = new MiddlewareApplication($callback);
	$response = $app->run();

	Assert::type(ResponseInterface::class, $response);
	Assert::equal(300, $response->getStatusCode());
});

// Send headers
Toolkit::test(function (): void {
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
Toolkit::test(function (): void {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response): void {
		throw new RuntimeException('Oh mama');
	};

	$app = new MiddlewareApplication($callback);
	$app->addListener($app::LISTENER_ERROR, function (MiddlewareApplication $app, Throwable $e, ServerRequestInterface $req, ResponseInterface $res): void {
		Notes::add('CALLED');
	});

	Assert::throws(function () use ($app): void {
		$app->run();
	}, RuntimeException::class, 'Oh mama');
	Assert::equal(['CALLED'], Notes::fetch());
});

// Throws exception and catch
Toolkit::test(function (): void {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response): void {
		throw new RuntimeException('Oh mama');
	};

	$app = new MiddlewareApplication($callback);
	$app->setCatchExceptions(true);
	$app->addListener($app::LISTENER_ERROR, function (MiddlewareApplication $app, Throwable $e, ServerRequestInterface $req, ResponseInterface $res): void {
		Notes::add('CALLED');
	});

	$app->run();
	Assert::equal(['CALLED'], Notes::fetch());
});

// Throws exception and handle in onError
Toolkit::test(function (): void {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response): void {
		throw new RuntimeException('Oh mama');
	};

	$app = new MiddlewareApplication($callback);
	$app->addListener($app::LISTENER_ERROR, function (MiddlewareApplication $app, Throwable $e, ServerRequestInterface $req, ResponseInterface $res): ResponseInterface {
		Notes::add('CALLED');

		$res->getBody()->write('OK');

		return $res;
	});

	$res = $app->run();
	$res->getBody()->rewind();

	Assert::equal('OK', $res->getBody()->getContents());
	Assert::equal(['CALLED'], Notes::fetch());
});

// Dispatching events
Toolkit::test(function (): void {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
		$response->getBody()->write('OK');

		return $response;
	};

	$app = new MiddlewareApplication($callback);
	$app->addListener($app::LISTENER_STARTUP, function (MiddlewareApplication $app): void {
		Notes::add('STARTUP');
	});
	$app->addListener($app::LISTENER_REQUEST, function (MiddlewareApplication $app, ServerRequestInterface $req, ResponseInterface $res): void {
		Notes::add('REQUEST');
	});
	$app->addListener($app::LISTENER_RESPONSE, function (MiddlewareApplication $app, ServerRequestInterface $req, ResponseInterface $res): void {
		Notes::add('RESPONSE');
	});

	Assert::equal('OK', (string) $app->run()->getBody());
	Assert::equal(['STARTUP', 'REQUEST', 'RESPONSE'], Notes::fetch());
});

// Dispatching events with return value as parameter
Toolkit::test(function (): void {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
		$response->getBody()->write('OK');

		return $response;
	};

	$app = new MiddlewareApplication($callback);
	$app->addListener($app::LISTENER_STARTUP, function (MiddlewareApplication $app): string {
		Notes::add('STARTUP1');

		return '1';
	});
	$app->addListener($app::LISTENER_STARTUP, function (MiddlewareApplication $app, $prev): string {
		Notes::add('STARTUP2');
		Notes::add($prev);

		return '2';
	});
	$app->addListener($app::LISTENER_STARTUP, function (MiddlewareApplication $app, $prev): void {
		Notes::add('STARTUP3');
		Notes::add($prev);
	});

	Assert::equal('OK', (string) $app->run()->getBody());
	Assert::equal(['STARTUP1', 'STARTUP2', '1', 'STARTUP3', '2'], Notes::fetch());
});
