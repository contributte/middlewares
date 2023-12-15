<?php declare(strict_types = 1);

namespace Tests;

use Contributte\Middlewares\MethodOverrideMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Contributte\Tester\Toolkit;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

Toolkit::test(function (): void {
	$middleware = new MethodOverrideMiddleware();

	$request = Psr7ServerRequestFactory::fromGlobal()
		->withMethod('POST')
		->withHeader(MethodOverrideMiddleware::OVERRIDE_HEADER, 'PUT');

	$middleware(
		$request,
		Psr7ResponseFactory::fromGlobal(),
		function (ServerRequestInterface $req, ResponseInterface $res): ResponseInterface {
			Assert::equal('PUT', $req->getMethod());

			return $res;
		}
	);
});

Toolkit::test(function (): void {
	$middleware = new MethodOverrideMiddleware();

	$request = Psr7ServerRequestFactory::fromGlobal()
		->withMethod('POST')
		->withHeader(MethodOverrideMiddleware::OVERRIDE_HEADER, '');

	$middleware(
		$request,
		Psr7ResponseFactory::fromGlobal(),
		function (ServerRequestInterface $req, ResponseInterface $res): ResponseInterface {
			Assert::equal('POST', $req->getMethod());

			return $res;
		}
	);
});

Toolkit::test(function (): void {
	$middleware = new MethodOverrideMiddleware();

	$request = Psr7ServerRequestFactory::fromGlobal()
		->withMethod('DELETE');

	$middleware(
		$request,
		Psr7ResponseFactory::fromGlobal(),
		function (ServerRequestInterface $req, ResponseInterface $res): ResponseInterface {
			Assert::equal('DELETE', $req->getMethod());

			return $res;
		}
	);
});
