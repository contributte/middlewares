<?php

/**
 * Test: DI\NetteMiddlewareExtension
 */

use Contributte\Middlewares\Application\MiddlewareApplication;
use Contributte\Middlewares\DI\NetteMiddlewareExtension;
use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Middlewares\IMiddleware;
use Nette\Bridges\HttpDI\HttpExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Psr\Http\Message\ResponseInterface;
use Tester\Assert;
use Tester\FileMock;

require_once __DIR__ . '/../../bootstrap.php';

// Definition of middlewares
test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
		$compiler->addExtension('http', new HttpExtension());
		$compiler->addExtension('middleware', new NetteMiddlewareExtension());
		$compiler->loadConfig(FileMock::create('
			middleware:
				middlewares:
					- Tests\Fixtures\PassMiddleware
					- @middleware
			services:
				middleware: Tests\Fixtures\PassMiddleware
		', 'neon'));
	}, 1);

	/** @var Container $container */
	$container = new $class;

	Assert::count(2, $container->findByType(IMiddleware::class));
});

// Exception - no configuration
test(function () {
	ASsert::throws(function () {
		$loader = new ContainerLoader(TEMP_DIR, TRUE);
		$class = $loader->load(function (Compiler $compiler) {
			$compiler->addExtension('middleware', new NetteMiddlewareExtension());
		}, 2);

		/** @var Container $container */
		$container = new $class;
	}, InvalidStateException::class, 'There must be at least one middleware registered or root middleware configured.');
});

// Root middleware - defined as string
test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
		$compiler->addExtension('http', new HttpExtension());
		$compiler->addExtension('middleware', new NetteMiddlewareExtension());
		$compiler->loadConfig(FileMock::create('
			middleware:
				root: Tests\Fixtures\SimpleRootMiddleware
		', 'neon'));
	}, 3);

	/** @var Container $container */
	$container = new $class;

	Assert::count(0, $container->findByType(IMiddleware::class));
});

// Root middleware - defined as service
test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
		$compiler->addExtension('http', new HttpExtension());
		$compiler->addExtension('middleware', new NetteMiddlewareExtension());
		$compiler->loadConfig(FileMock::create('
			middleware:
				root: @root
				
			services:
				root: Tests\Fixtures\SimpleRootMiddleware
		', 'neon'));
	}, 4);

	/** @var Container $container */
	$container = new $class;

	Assert::count(1, $container->findByType(IMiddleware::class));

	/** @var MiddlewareApplication $app */
	$app = $container->getByType(MiddlewareApplication::class);
	$res = $app->run();

	Assert::type(ResponseInterface::class, $res);
});
