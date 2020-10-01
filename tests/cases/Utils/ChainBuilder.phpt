<?php declare(strict_types = 1);

/**
 * Test: Utils\ChainBuilder
 */

use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Middlewares\Utils\ChainBuilder;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Ninjify\Nunjuck\Notes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Chain calling
test(function (): void {
	$builder = new ChainBuilder();

	$builder->add(function (ServerRequestInterface $req, ResponseInterface $res, callable $next): ResponseInterface {
		Notes::add('A');
		$res = $next($req, $res);
		Notes::add('A');

		return $res;
	});

	$builder->add(function (ServerRequestInterface $req, ResponseInterface $res, callable $next): ResponseInterface {
		Notes::add('B');
		$res = $next($req, $res);
		Notes::add('B');

		return $res;
	});

	$builder->add(function (ServerRequestInterface $req, ResponseInterface $res, callable $next): ResponseInterface {
		Notes::add('C');
		$res = $next($req, $res);
		Notes::add('C');

		return $res;
	});

	$cb = $builder->create();
	$cb(Psr7ServerRequestFactory::fromSuperGlobal(), Psr7ResponseFactory::fromGlobal());

	Assert::equal([
		'A',
		'B',
		'C',
		'C',
		'B',
		'A',
	], Notes::fetch());
});

// Chain exceptions
test(function (): void {
	$builder = new ChainBuilder();

	Assert::throws(function () use ($builder): void {
		$builder->create();
	}, InvalidStateException::class, 'At least one middleware is needed');
});

// Factory
test(function (): void {
	$middleware = ChainBuilder::factory([
		function (ServerRequestInterface $req, ResponseInterface $res, callable $next): ResponseInterface {
			Notes::add('A');
			$res = $next($req, $res);
			Notes::add('A');

			return $res;
		},
	]);

	$response = $middleware(Psr7ServerRequestFactory::fromSuperGlobal(), Psr7ResponseFactory::fromGlobal());
	Assert::equal(['A', 'A'], Notes::fetch());
});
