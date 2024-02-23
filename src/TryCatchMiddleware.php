<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

class TryCatchMiddleware implements IMiddleware
{

	private bool $catch = true;

	private bool $debug = false;

	private ?LoggerInterface $logger = null;

	private string $logLevel;

	public function setCatchExceptions(bool $catch): void
	{
		$this->catch = $catch;
	}

	public function setDebugMode(bool $debug): void
	{
		$this->debug = $debug;
	}

	public function setLogger(LoggerInterface $logger, string $logLevel = LogLevel::ERROR): void
	{
		$this->logger = $logger;
		$this->logLevel = $logLevel;
	}

	protected function log(Throwable $throwable): void
	{
		if ($this->logger === null) {
			return;
		}

		$this->logger->log($this->logLevel, $throwable->getMessage(), ['exception' => $throwable]);
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
	{
		if (!$this->debug || $this->catch) { // Always catch if not debug and catch optionally if is debug
			try {
				$response = $next($request, $response);

				return $response;
			} catch (Throwable $throwable) {
				$this->log($throwable);
				$response = $response->withStatus(500);
				$response->getBody()->write('Application encountered an internal error. Please try again later.');

				return $response;
			}
		}

		return $next($request, $response);
	}

}
