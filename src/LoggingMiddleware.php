<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class LoggingMiddleware implements IMiddleware
{

	public function __construct(
		private readonly LoggerInterface $logger,
	)
	{
	}

	/**
	 * @param callable(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface $next
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
	{
		$this->logger->info(sprintf('Requested url: %s', $request->getUri()->withUserInfo('', null)));

		return $next($request, $response);
	}

}
