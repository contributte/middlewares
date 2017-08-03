<?php

/**
 * Test: Utils\ChainBuilder
 */

use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Middlewares\Utils\ChainBuilder;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Ninjify\Nunjuck\Notes;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Chain calling
test(function () {
	$builder = new ChainBuilder();

	$builder->add(function ($req, $res, callable $next) {
		Notes::add('A');
		$res = $next($req, $res);
		Notes::add('A');

		return $res;
	});

	$builder->add(function ($req, $res, callable $next) {
		Notes::add('B');
		$res = $next($req, $res);
		Notes::add('B');

		return $res;
	});

	$builder->add(function ($req, $res, callable $next) {
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
test(function () {
	$builder = new ChainBuilder();

	Assert::throws(function () use ($builder) {
		$builder->add('foobar');
	}, InvalidStateException::class, 'Middleware is not callable');

	Assert::throws(function () use ($builder) {
		$builder->create();
	}, InvalidStateException::class, 'At least one middleware is needed');
});
