<?php

/**
 * Test: TracyMiddleware
 */

use Contributte\Middlewares\TracyMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;
use Tracy\Debugger;

require_once __DIR__ . '/../bootstrap.php';

// Disabled
test(function () {
	Assert::exception(function () {
		$middleware = TracyMiddleware::factory(TRUE);
		$middleware->disable();
		$middleware(Psr7ServerRequestFactory::fromSuperGlobal(), Psr7ResponseFactory::fromGlobal(), function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response) {
			throw new RuntimeException('Foobar');
		});
	}, RuntimeException::class, 'Foobar');
});

// Warnings
test(function () {
	$middleware = TracyMiddleware::factory(TRUE);
	$middleware->setMode(Debugger::PRODUCTION);
	$middleware->setLogDir(TEMP_DIR);
	$middleware->setEmail('dev@contributte.org');

	$middleware(Psr7ServerRequestFactory::fromSuperGlobal(), Psr7ResponseFactory::fromGlobal(), function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response) {
		$a++;
	});

	Assert::match('%a%PHP Notice: Undefined variable: a in %a%', file_get_contents(TEMP_DIR . '/error.log'));
});
