<?php declare(strict_types = 1);

use Contributte\Middlewares\DI\MiddlewaresExtension;
use Contributte\Middlewares\DI\NetteMiddlewaresExtension;
use Contributte\Middlewares\IMiddleware;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Nette\Bridges\HttpDI\HttpExtension;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder as NetteContainerBuilder;
use Nette\DI\ServiceCreationException;
use Nette\Http\Request;
use Nette\Http\RequestFactory;
use Tester\Assert;
use Tests\Fixtures\MutableExtension;

require_once __DIR__ . '/../../bootstrap.php';

// Definition of middlewares
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('http', new HttpExtension());
			$compiler->addExtension('middleware', new NetteMiddlewaresExtension());
			$compiler->addConfig(Neonkit::load(<<<'NEON'
				middleware:
					middlewares:
						- Tests\Fixtures\PassMiddleware
						- @middleware
				services:
					middleware: Tests\Fixtures\PassMiddleware
			NEON
			));
		})->build();

	Assert::count(2, $container->findByType(IMiddleware::class));
});

// Definition of middlewares
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('http', new HttpExtension());
			$compiler->addExtension('middleware', new MiddlewaresExtension());
			$compiler->addConfig(Neonkit::load(<<<'NEON'
				middleware:
					middlewares:
						b: @middleware
						c: Tests\Fixtures\CompositeMiddleware([
							Tests\Fixtures\PassMiddleware(),
							Tests\Fixtures\PassMiddleware(),
							Tests\Fixtures\PassMiddleware()
						])
				services:
					middleware: Tests\Fixtures\PassMiddleware
			NEON
			));
		})->build();

	Assert::count(2, $container->findByType(IMiddleware::class));
});

// Misssing nette/http request
Toolkit::test(function (): void {
	Assert::throws(function (): void {
		ContainerBuilder::of()
			->withCompiler(function (Compiler $compiler): void {
				$compiler->addExtension('middleware', new NetteMiddlewaresExtension());
			})->build();
	}, ServiceCreationException::class, 'Extension needs service Nette\Http\Request. Do you have nette/http in composer file?');
});

// Misssing nette/http response
Toolkit::test(function (): void {
	Assert::throws(function (): void {
		ContainerBuilder::of()
			->withCompiler(function (Compiler $compiler): void {
				$mutable = new MutableExtension();
				$mutable->onLoad[] = function (CompilerExtension $ext, NetteContainerBuilder $builder): void {
					$builder->addDefinition($ext->prefix('request'))
						->setType(Request::class)
						->setFactory(RequestFactory::class . '::createHttpRequest');
				};
				$compiler->addExtension('x', $mutable);
				$compiler->addExtension('middleware', new NetteMiddlewaresExtension());
			})->build();
	}, ServiceCreationException::class, 'Extension needs service Nette\Http\Response. Do you have nette/http in composer file?');
});

// Priority middlewares
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('http', new HttpExtension());
			$compiler->addExtension('middleware', new MiddlewaresExtension());
			$compiler->addConfig(Neonkit::load(<<<'NEON'
				services:
					foo1: {class: Tests\Fixtures\PassMiddleware, tags: {middleware: {priority: 100}}}
					foo2: {class: Tests\Fixtures\PassMiddleware, tags: {middleware: {priority: 200}}}
					foo3: {class: Tests\Fixtures\PassMiddleware, tags: {middleware: {priority: 300}}}
			NEON
			));
		})->build();

	Assert::count(3, $container->findByType(IMiddleware::class));
	Assert::count(3, $container->findByTag(MiddlewaresExtension::MIDDLEWARE_TAG));

	$search = $container->findByTag(MiddlewaresExtension::MIDDLEWARE_TAG);

	Assert::equal(['priority' => 100], $search['foo1']);
	Assert::equal(['priority' => 200], $search['foo2']);
	Assert::equal(['priority' => 300], $search['foo3']);
});
