<?php

namespace Contributte\Middlewares\Middleware;

use Contributte\Middlewares\Utils\Lambda;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
abstract class AbstractRootMiddleware extends BaseMiddleware
{

	/**
	 * @param ServerRequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @param callable $next
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next)
	{
		// Create chain of middlewares
		$chain = $this->create();

		// Process chain
		$psr7Response = call_user_func_array($chain, [$psr7Request, $psr7Response, Lambda::leaf()]);

		// Just return response, this is root middleware, no more next middlewares
		return $psr7Response;
	}

	/**
	 * @return callable
	 */
	abstract protected function create();

}
