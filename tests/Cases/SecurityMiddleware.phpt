<?php declare(strict_types = 1);

use Contributte\Middlewares\Security\DebugAuthenticator;
use Contributte\Middlewares\SecurityMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\Notes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

// Success auth
Toolkit::test(function (): void {
	$middleware = new SecurityMiddleware(new DebugAuthenticator('FOOBAR'));
	$middleware(Psr7ServerRequestFactory::fromSuperGlobal(), Psr7ResponseFactory::fromGlobal(), function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): ResponseInterface {
		Notes::add('CALLED');
		Notes::add($psr7Request->getAttribute(SecurityMiddleware::ATTR_IDENTITY));

		return $psr7Response;
	});

	Assert::equal(['CALLED', 'FOOBAR'], Notes::fetch());
});

// No auth
Toolkit::test(function (): void {
	$middleware = new SecurityMiddleware(new DebugAuthenticator(false));
	$middleware(Psr7ServerRequestFactory::fromSuperGlobal(), Psr7ResponseFactory::fromGlobal(), function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): ResponseInterface {
		Notes::add('CALLED');

		return $psr7Response;
	});

	Assert::equal([], Notes::fetch());
});
