<?php

/**
 * Test: BuilderMiddleware
 */

use Contributte\Middlewares\BuilderMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Ninjify\Nunjuck\Notes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

// Build chain of middlewares
test(function () {
	$middleware = new BuilderMiddleware();
	$middleware->add(function (ServerRequestInterface $req, ResponseInterface $res, callable $next) {
		Notes::add('A');
		$res = $next($req, $res);
		Notes::add('A');

		return $res;
	});
	$middleware->add(function (ServerRequestInterface $req, ResponseInterface $res, callable $next) {
		Notes::add('B');
		$res = $next($req, $res);
		Notes::add('B');

		return $res;
	});
	$middleware->add(function (ServerRequestInterface $req, ResponseInterface $res, callable $next) {
		Notes::add('C');
		$res = $next($req, $res);
		Notes::add('C');

		return $res;
	});

	$middleware(Psr7ServerRequestFactory::fromSuperGlobal(), Psr7ResponseFactory::fromGlobal(), function () {
		Notes::add('END');
	});

	Assert::equal([
		'A',
		'B',
		'C',
		'C',
		'B',
		'A',
		'END',
	], Notes::fetch());
});
