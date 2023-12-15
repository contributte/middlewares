<?php declare(strict_types = 1);

use Contributte\Middlewares\LoggingMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Contributte\Tester\Toolkit;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

Toolkit::test(function (): void {
	$logger = new class implements LoggerInterface
	{

		private string $url = '';

		/**
		 * @param mixed[] $context
		 */
		public function emergency(string|Stringable $message, array $context = []): void
		{
			// Noop
		}

		/**
		 * @param mixed[] $context
		 */
		public function alert(string|Stringable $message, array $context = []): void
		{
			// Noop
		}

		/**
		 * @param mixed[] $context
		 */
		public function critical(string|Stringable $message, array $context = []): void
		{
			// Noop
		}

		/**
		 * @param mixed[] $context
		 */
		public function error(string|Stringable $message, array $context = []): void
		{
			// Noop
		}

		/**
		 * @param mixed[] $context
		 */
		public function warning(string|Stringable $message, array $context = []): void
		{
			// Noop
		}

		/**
		 * @param mixed[] $context
		 */
		public function notice(string|Stringable $message, array $context = []): void
		{
			// Noop
		}

		/**
		 * @param mixed[] $context
		 */
		public function info(string|Stringable $message, array $context = []): void
		{
			$this->url = $message;
		}

		/**
		 * @param mixed[] $context
		 */
		public function debug(string|Stringable $message, array $context = []): void
		{
			// Noop
		}

		/**
		 * @param mixed[] $context
		 */
		public function log(mixed $level, string|Stringable $message, array $context = []): void
		{
			// Noop
		}

		public function get(): string
		{
			return $this->url;
		}

	};

	$response = Psr7ResponseFactory::fromGlobal();
	$middleware = new LoggingMiddleware($logger);
	$response = $middleware(
		Psr7ServerRequestFactory::fromSuperGlobal()->withNewUri('https://user:password@example.com/foo/bar'),
		$response,
		fn (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): ResponseInterface => $psr7Response
	);

	Assert::same('Requested url: https://example.com/foo/bar', $logger->get());

	$response = Psr7ResponseFactory::fromGlobal();
	$middleware = new LoggingMiddleware($logger);
	$response = $middleware(
		Psr7ServerRequestFactory::fromSuperGlobal()->withNewUri('https://user@example.com/foo/bar'),
		$response,
		fn (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): ResponseInterface => $psr7Response
	);

	Assert::same('Requested url: https://example.com/foo/bar', $logger->get());

	$response = Psr7ResponseFactory::fromGlobal();
	$middleware = new LoggingMiddleware($logger);
	$response = $middleware(
		Psr7ServerRequestFactory::fromSuperGlobal()->withNewUri('https://example.com/foo/bar'),
		$response,
		fn (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): ResponseInterface => $psr7Response
	);

	Assert::same('Requested url: https://example.com/foo/bar', $logger->get());
});
