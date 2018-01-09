<?php

namespace Tests;

use Contributte\Middlewares\MethodOverrideMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

test(function () {
	$middleware = new MethodOverrideMiddleware();

	$request = Psr7ServerRequestFactory::fromGlobal()
		->withMethod('POST')
		->withHeader(MethodOverrideMiddleware::OVERRIDE_HEADER, 'PUT');

	$middleware(
		$request,
		Psr7ResponseFactory::fromGlobal(),
		function (ServerRequestInterface $req, ResponseInterface $res) {
			Assert::equal('PUT', $req->getMethod());
		});
});

test(function () {
	$middleware = new MethodOverrideMiddleware();

	$request = Psr7ServerRequestFactory::fromGlobal()
		->withMethod('POST')
		->withHeader(MethodOverrideMiddleware::OVERRIDE_HEADER, '');

	$middleware(
		$request,
		Psr7ResponseFactory::fromGlobal(),
		function (ServerRequestInterface $req, ResponseInterface $res) {
			Assert::equal('POST', $req->getMethod());
		});
});

test(function () {
	$middleware = new MethodOverrideMiddleware();

	$request = Psr7ServerRequestFactory::fromGlobal()
		->withMethod('DELETE');

	$middleware(
		$request,
		Psr7ResponseFactory::fromGlobal(),
		function (ServerRequestInterface $req, ResponseInterface $res) {
			Assert::equal('DELETE', $req->getMethod());
		});
});
