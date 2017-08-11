<?php

/**
 * Test: TracyMiddleware [catch exception]
 *
 * @exitCode 255
 * @httpCode 500
 * @outputMatch %A?%OK!
 * @phpVersion >= 7.0
 */

use Contributte\Middlewares\TracyMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;
use Tracy\Debugger;

require_once __DIR__ . '/../bootstrap.php';

test(function () {
	$middleware = TracyMiddleware::factory(TRUE);
	$middleware->setMode(Debugger::DEVELOPMENT);
	$middleware->setEmail('dev@contributte.org');
	$middleware->setLogDir(TEMP_DIR);

	register_shutdown_function(function () {
		Assert::match('%a%Error: Call to undefined function missing_function() in %a%', file_get_contents(Debugger::$logDirectory . '/exception.log'));
		Assert::true(is_file(Debugger::$logDirectory . '/email-sent'));
		echo 'OK!'; // prevents PHP bug #62725
	});

	$middleware(Psr7ServerRequestFactory::fromSuperGlobal(), Psr7ResponseFactory::fromGlobal(), function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response) {
		missing_function();
	});
});
