<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class TryCatchMiddleware implements IMiddleware
{

	/** @var bool */
	private $catch = true;

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
	{
		if ($this->catch) {
			try {
				$response = $next($request, $response);
				return $response;
			} catch (Throwable $throwable) {
				$response = $response->withStatus(500);
				$response->getBody()->write('Application encountered an internal error. Please try again later.');
				return $response;
			}
		}

		return $next($request, $response);
	}

	public function setCatchExceptions(bool $catch): void
	{
		$this->catch = $catch;
	}

}
