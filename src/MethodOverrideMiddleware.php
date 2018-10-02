<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MethodOverrideMiddleware implements IMiddleware
{

	public const OVERRIDE_HEADER = 'X-HTTP-Method-Override';

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
	{
		if ($request->hasHeader(self::OVERRIDE_HEADER) && $request->getHeader(self::OVERRIDE_HEADER)[0] !== '') {
			$request = $request->withMethod($request->getHeader(self::OVERRIDE_HEADER)[0]);
		}

		return $next($request, $response);
	}

}
