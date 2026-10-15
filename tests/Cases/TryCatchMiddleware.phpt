<?php declare(strict_types = 1);

use Contributte\Middlewares\TryCatchMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Contributte\Tester\Toolkit;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LogLevel;
use Tester\Assert;
use Tests\Fixtures\MemoryLogger;

require_once __DIR__ . '/../bootstrap.php';

// Catched expception, production mode
Toolkit::test(function (): void {
	$response = Psr7ResponseFactory::fromGlobal();
	$middleware = new TryCatchMiddleware();
	$middleware->setDebugMode(false);
	$middleware->setCatchExceptions(true);
	$response = $middleware(
		Psr7ServerRequestFactory::fromSuperGlobal(),
		$response,
		function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): void {
			throw new RuntimeException('foo');
		}
	);

	ob_start();
	$stream = $response->getBody();
	$stream->rewind();
	while (!$stream->eof()) {
		echo $stream->read(8192);
	}

	Assert::same(500, $response->getStatusCode());
	Assert::same('Application encountered an internal error. Please try again later.', ob_get_clean());
});

// Catched expception, debug mode with catch exception enabled
Toolkit::test(function (): void {
	$response = Psr7ResponseFactory::fromGlobal();
	$middleware = new TryCatchMiddleware();
	$middleware->setDebugMode(true);
	$middleware->setCatchExceptions(true);
	$response = $middleware(
		Psr7ServerRequestFactory::fromSuperGlobal(),
		$response,
		function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): void {
			throw new RuntimeException('foo');
		}
	);

	ob_start();
	$stream = $response->getBody();
	$stream->rewind();
	while (!$stream->eof()) {
		echo $stream->read(8192);
	}

	Assert::same(500, $response->getStatusCode());
	Assert::same('Application encountered an internal error. Please try again later.', ob_get_clean());
});

// Ok
Toolkit::test(function (): void {
	$response = Psr7ResponseFactory::fromGlobal();
	$middleware = new TryCatchMiddleware();
	$middleware->setCatchExceptions(false);
	$response = $middleware(
		Psr7ServerRequestFactory::fromSuperGlobal(),
		$response,
		function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): ResponseInterface {
			$psr7Response->getBody()->write('foo');

			return $psr7Response;
		}
	);

	ob_start();
	$stream = $response->getBody();
	$stream->rewind();
	while (!$stream->eof()) {
		echo $stream->read(8192);
	}

	Assert::same('foo', ob_get_clean());
});

// Disabled
Toolkit::test(function (): void {
	Assert::exception(function (): void {
		$middleware = new TryCatchMiddleware();
		$middleware->setDebugMode(true);
		$middleware->setCatchExceptions(false);
		$middleware(
			Psr7ServerRequestFactory::fromSuperGlobal(),
			Psr7ResponseFactory::fromGlobal(),
			function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): void {
				throw new RuntimeException('foo');
			}
		);
	}, RuntimeException::class, 'foo');
});

// Logging, caught exception is logged with default level
Toolkit::test(function (): void {
	$logger = new MemoryLogger();
	$exception = new RuntimeException('foo');
	$middleware = new TryCatchMiddleware();
	$middleware->setLogger($logger);
	$response = $middleware(
		Psr7ServerRequestFactory::fromSuperGlobal(),
		Psr7ResponseFactory::fromGlobal(),
		function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response) use ($exception): void {
			throw $exception;
		}
	);

	Assert::same(500, $response->getStatusCode());
	Assert::count(1, $logger->logs);
	Assert::same(LogLevel::ERROR, $logger->logs[0]['level']);
	Assert::same('foo', $logger->logs[0]['message']);
	Assert::same(['exception' => $exception], $logger->logs[0]['context']);
});

// Logging, custom log level
Toolkit::test(function (): void {
	$logger = new MemoryLogger();
	$middleware = new TryCatchMiddleware();
	$middleware->setLogger($logger, LogLevel::CRITICAL);
	$middleware(
		Psr7ServerRequestFactory::fromSuperGlobal(),
		Psr7ResponseFactory::fromGlobal(),
		function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): void {
			throw new RuntimeException('foo');
		}
	);

	Assert::count(1, $logger->logs);
	Assert::same(LogLevel::CRITICAL, $logger->logs[0]['level']);
});

// Logging, nothing is logged without exception
Toolkit::test(function (): void {
	$logger = new MemoryLogger();
	$middleware = new TryCatchMiddleware();
	$middleware->setLogger($logger);
	$response = $middleware(
		Psr7ServerRequestFactory::fromSuperGlobal(),
		Psr7ResponseFactory::fromGlobal(),
		fn (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): ResponseInterface => $psr7Response
	);

	Assert::same(200, $response->getStatusCode());
	Assert::same([], $logger->logs);
});

// Logging, uncaught exception in debug mode is not logged
Toolkit::test(function (): void {
	$logger = new MemoryLogger();
	$middleware = new TryCatchMiddleware();
	$middleware->setLogger($logger);
	$middleware->setDebugMode(true);
	$middleware->setCatchExceptions(false);

	Assert::exception(function () use ($middleware): void {
		$middleware(
			Psr7ServerRequestFactory::fromSuperGlobal(),
			Psr7ResponseFactory::fromGlobal(),
			function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): void {
				throw new RuntimeException('foo');
			}
		);
	}, RuntimeException::class, 'foo');

	Assert::same([], $logger->logs);
});
