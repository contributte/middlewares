<?php

/**
 * Test: SecurityMiddleware
 */

use Contributte\Middlewares\Security\DebugAuthenticator;
use Contributte\Middlewares\SecurityMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Ninjify\Nunjuck\Notes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

// Success auth
test(function () {
	$middleware = new SecurityMiddleware(new DebugAuthenticator('FOOBAR'));
	$middleware(Psr7ServerRequestFactory::fromSuperGlobal(), Psr7ResponseFactory::fromGlobal(), function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response) {
		Notes::add('CALLED');
		Notes::add($psr7Request->getAttribute(SecurityMiddleware::ATTR_IDENTITY));
	});

	Assert::equal(['CALLED', 'FOOBAR'], Notes::fetch());
});

// No auth
test(function () {
	$middleware = new SecurityMiddleware(new DebugAuthenticator(FALSE));
	$middleware(Psr7ServerRequestFactory::fromSuperGlobal(), Psr7ResponseFactory::fromGlobal(), function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response) {
		Notes::add('CALLED');
	});

	Assert::equal([], Notes::fetch());
});
