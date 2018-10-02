<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EnforceHttpsMiddleware implements IMiddleware
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
	{
		if (strtolower($request->getUri()->getScheme()) !== 'https') {
			$response = $response->withStatus(400);
			$response->getBody()->write('Encrypted connection is required. Please use https connection.');
			return $response;
		}

		// Pass to next middleware
		return $next($request, $response);
	}

}
