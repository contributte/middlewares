<?php

/**
 * Test: TracyMiddleware [catch exception without exit]
 *
 * @exitCode 0
 * @outputMatch RuntimeException: Foobar in%A%
 */

use Contributte\Middlewares\TracyMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/../bootstrap.php';

test(function () {
	$middleware = TracyMiddleware::factory(TRUE);
	$middleware->setAutoExit(FALSE);
	$middleware(Psr7ServerRequestFactory::fromSuperGlobal(), Psr7ResponseFactory::fromGlobal(), function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response) {
		throw new RuntimeException('Foobar');
	});
});
