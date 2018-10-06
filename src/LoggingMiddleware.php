<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class LoggingMiddleware implements IMiddleware
{

	/** @var LoggerInterface */
	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
	{
		$uri = $request->getUri();
		if ($uri->getUserInfo() !== '') {
			[$user] = explode(':', $uri->getUserInfo());
			$uri = $uri->withUserInfo($user, ''); // Remove password for security reasons
		}
		$this->logger->info((string) $uri);

		return $next($request, $response);
	}

}
