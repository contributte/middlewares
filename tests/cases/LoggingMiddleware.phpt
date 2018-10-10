<?php declare(strict_types = 1);

/**
 * Test: LoggingMiddleware
 */

use Contributte\Middlewares\LoggingMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

test(function (): void {
	$logger = new class implements LoggerInterface
	{

		/** @var string */
		private $url = '';

		/**
		 * @param string $message
		 * @param mixed[] $context
		 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
		 */
		public function emergency($message, array $context = []): void
		{
		}

		/**
		 * @param string $message
		 * @param mixed[] $context
		 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
		 */
		public function alert($message, array $context = []): void
		{
		}

		/**
		 * @param string $message
		 * @param mixed[] $context
		 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
		 */
		public function critical($message, array $context = []): void
		{
		}

		/**
		 * @param string $message
		 * @param mixed[] $context
		 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
		 */
		public function error($message, array $context = []): void
		{
		}

		/**
		 * @param string $message
		 * @param mixed[] $context
		 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
		 */
		public function warning($message, array $context = []): void
		{
		}

		/**
		 * @param string $message
		 * @param mixed[] $context
		 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
		 */
		public function notice($message, array $context = []): void
		{
		}

		/**
		 * @param string $message
		 * @param mixed[] $context
		 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
		 */
		public function info($message, array $context = []): void
		{
			$this->url = $message;
		}

		/**
		 * @param string $message
		 * @param mixed[] $context
		 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
		 */
		public function debug($message, array $context = []): void
		{
		}

		/**
		 * @param string $message
		 * @param mixed[] $context
		 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
		 */
		public function log($level, $message, array $context = []): void
		{
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
		function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): ResponseInterface {
			return $psr7Response;
		}
	);

	Assert::same('Requested url: https://user@example.com/foo/bar', $logger->get());

	$response = Psr7ResponseFactory::fromGlobal();
	$middleware = new LoggingMiddleware($logger);
	$response = $middleware(
		Psr7ServerRequestFactory::fromSuperGlobal()->withNewUri('https://example.com/foo/bar'),
		$response,
		function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): ResponseInterface {
			return $psr7Response;
		}
	);

	Assert::same('Requested url: https://example.com/foo/bar', $logger->get());
});
