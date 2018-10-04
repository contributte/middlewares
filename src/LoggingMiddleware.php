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
		[$user, $password] = explode(':', $uri->getUserInfo());
		$loggableUri = (string) $uri->withUserInfo($user, ''); // Remove password for security reasons
		$this->logger->info($loggableUri);

		return $next($request, $response);
	}

}
