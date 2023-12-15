<?php declare(strict_types = 1);

use Contributte\Middlewares\BuilderMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\Notes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

// Build chain of middlewares
Toolkit::test(function (): void {
	$middleware = new BuilderMiddleware();
	$middleware->add(function (ServerRequestInterface $req, ResponseInterface $res, callable $next): ResponseInterface {
		Notes::add('A');
		$res = $next($req, $res);
		Notes::add('A');

		return $res;
	});
	$middleware->add(function (ServerRequestInterface $req, ResponseInterface $res, callable $next): ResponseInterface {
		Notes::add('B');
		$res = $next($req, $res);
		Notes::add('B');

		return $res;
	});
	$middleware->add(function (ServerRequestInterface $req, ResponseInterface $res, callable $next): ResponseInterface {
		Notes::add('C');
		$res = $next($req, $res);
		Notes::add('C');

		return $res;
	});

	$middleware(Psr7ServerRequestFactory::fromSuperGlobal(), Psr7ResponseFactory::fromGlobal(), function (ServerRequestInterface $req, ResponseInterface $res): ResponseInterface {
		Notes::add('END');

		return $res;
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
