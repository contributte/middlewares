<?php declare(strict_types = 1);

use Contributte\Middlewares\Utils\Lambda;
use Contributte\Psr7\Psr7RequestFactory;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Tester\Toolkit;
use Psr\Http\Message\ResponseInterface;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Blank
Toolkit::test(function (): void {
	$fn = Lambda::blank();
	Assert::null($fn());
});

// Blank
Toolkit::test(function (): void {
	$fn = Lambda::leaf();
	Assert::type(ResponseInterface::class, $fn(Psr7RequestFactory::fromGlobal(), Psr7ResponseFactory::fromGlobal()));
});
