<?php declare(strict_types = 1);

/**
 * Test: DI\NetteMiddlewareExtension + DI\MiddlewareExtension
 */

use Contributte\Middlewares\DI\MiddlewaresExtension;
use Contributte\Middlewares\DI\NetteMiddlewaresExtension;
use Contributte\Middlewares\IMiddleware;
use Nette\Bridges\HttpDI\HttpExtension;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Container;
use Nette\DI\ContainerBuilder;
use Nette\DI\ContainerLoader;
use Nette\DI\ServiceCreationException;
use Nette\Http\Request;
use Nette\Http\RequestFactory;
use Tester\Assert;
use Tester\FileMock;
use Tests\Fixtures\MutableExtension;

require_once __DIR__ . '/../../bootstrap.php';

// Definition of middlewares
test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('http', new HttpExtension());
		$compiler->addExtension('middleware', new NetteMiddlewaresExtension());
		$compiler->loadConfig(FileMock::create('
			middleware:
				middlewares:
					- Tests\Fixtures\PassMiddleware
					- @middleware
			services:
				middleware: Tests\Fixtures\PassMiddleware
		', 'neon'));
	}, '1a');

	/** @var Container $container */
	$container = new $class();

	Assert::count(2, $container->findByType(IMiddleware::class));
});

// Definition of middlewares
test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('http', new HttpExtension());
		$compiler->addExtension('middleware', new MiddlewaresExtension());
		$compiler->loadConfig(FileMock::create('
			middleware:
				middlewares:
					a:
						class: Tests\Fixtures\PassMiddleware
					b: @middleware
					c: Tests\Fixtures\CompositeMiddleware([
						Tests\Fixtures\PassMiddleware(),
						Tests\Fixtures\PassMiddleware(),
						Tests\Fixtures\PassMiddleware()
					])
			services:
				middleware: Tests\Fixtures\PassMiddleware
		', 'neon'));
	}, '1b');

	/** @var Container $container */
	$container = new $class();

	Assert::count(3, $container->findByType(IMiddleware::class));
});

// Misssing nette/http request
test(function (): void {
	Assert::throws(function (): void {
		$loader = new ContainerLoader(TEMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('middleware', new NetteMiddlewaresExtension());
		}, 5);
		new $class();
	}, ServiceCreationException::class, 'Extension needs service Nette\Http\Request. Do you have nette/http in composer file?');
});

// Misssing nette/http response
test(function (): void {
	Assert::throws(function (): void {
		$loader = new ContainerLoader(TEMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$mutable = new MutableExtension();
			$mutable->onLoad[] = function (CompilerExtension $ext, ContainerBuilder $builder): void {
				$builder->addDefinition($ext->prefix('request'))
					->setType(Request::class)
					->setFactory(RequestFactory::class . '::createHttpRequest');
			};
			$compiler->addExtension('x', $mutable);
			$compiler->addExtension('middleware', new NetteMiddlewaresExtension());
		}, 6);

		new $class();
	}, ServiceCreationException::class, 'Extension needs service Nette\Http\Response. Do you have nette/http in composer file?');
});

// Priority middlewares
test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('http', new HttpExtension());
		$compiler->addExtension('middleware', new MiddlewaresExtension());
		$compiler->loadConfig(FileMock::create('
			services:
				foo1: {class: Tests\Fixtures\PassMiddleware, tags: {middleware: {priority: 100}}}
				foo2: {class: Tests\Fixtures\PassMiddleware, tags: {middleware: {priority: 200}}}
				foo3: {class: Tests\Fixtures\PassMiddleware, tags: {middleware: {priority: 300}}}
		', 'neon'));
	}, '4');

	/** @var Container $container */
	$container = new $class();

	Assert::count(3, $container->findByType(IMiddleware::class));
	Assert::count(3, $container->findByTag(MiddlewaresExtension::MIDDLEWARE_TAG));

	$search = $container->findByTag(MiddlewaresExtension::MIDDLEWARE_TAG);

	Assert::equal(['priority' => 100], $search['foo1']);
	Assert::equal(['priority' => 200], $search['foo2']);
	Assert::equal(['priority' => 300], $search['foo3']);
});
