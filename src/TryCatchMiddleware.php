<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class TryCatchMiddleware extends BaseMiddleware
{

	/** @var bool */
	private $enable = true;

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
	{
		if ($this->enable) {
			try {
				$response = $next($request, $response);
				return $response;
			} catch (Throwable $throwable) {
				$response = $response->withStatus(500);
				$response->getBody()->write(sprintf('Application encountered an internal error with status code "500" and with message "%s".', $throwable->getMessage()));
				return $response;
			}
		}

		return $next($request, $response);
	}

	public function enable(): void
	{
		$this->enable = true;
	}

	public function disable(): void
	{
		$this->enable = false;
	}

}
