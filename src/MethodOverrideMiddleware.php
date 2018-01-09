<?php

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MethodOverrideMiddleware extends BaseMiddleware
{

	const OVERRIDE_HEADER = 'X-HTTP-Method-Override';

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param callable $next
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
	{
		if ($request->hasHeader(self::OVERRIDE_HEADER) && $request->getHeader(self::OVERRIDE_HEADER)[0] !== '') {
			$request = $request->withMethod($request->getHeader(self::OVERRIDE_HEADER)[0]);
		}

		return $next($request, $response);
	}

}
