<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Contributte\Middlewares\Utils\Lambda;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractRootMiddleware implements IMiddleware
{

	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next): ResponseInterface
	{
		// Create chain of middlewares
		$chain = $this->create();

		// Process chain
		$psr7Response = call_user_func_array($chain, [$psr7Request, $psr7Response, Lambda::leaf()]);

		// Just return response, this is root middleware, no more next middlewares
		return $psr7Response;
	}

	abstract protected function create(): callable;

}
