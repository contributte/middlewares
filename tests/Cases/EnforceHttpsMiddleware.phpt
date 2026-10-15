<?php declare(strict_types = 1);

use Contributte\Middlewares\EnforceHttpsMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\Notes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

// HTTPS request is passed to next middleware
Toolkit::test(function (): void {
	$middleware = new EnforceHttpsMiddleware();
	$request = Psr7ServerRequestFactory::fromGlobal();
	$request = $request->withUri($request->getUri()->withScheme('https'));

	$response = $middleware($request, Psr7ResponseFactory::fromGlobal(), function (ServerRequestInterface $req, ResponseInterface $res): ResponseInterface {
		Notes::add('CALLED');

		return $res;
	});

	Assert::equal(['CALLED'], Notes::fetch());
	Assert::equal(200, $response->getStatusCode());
});

// HTTP request is rejected
Toolkit::test(function (): void {
	$middleware = new EnforceHttpsMiddleware();
	$request = Psr7ServerRequestFactory::fromGlobal();
	$request = $request->withUri($request->getUri()->withScheme('http'));

	$response = $middleware($request, Psr7ResponseFactory::fromGlobal(), function (ServerRequestInterface $req, ResponseInterface $res): ResponseInterface {
		Notes::add('CALLED');

		return $res;
	});

	Assert::equal([], Notes::fetch());
	Assert::equal(400, $response->getStatusCode());
	Assert::equal('Encrypted connection is required. Please use https connection.', (string) $response->getBody());
});
