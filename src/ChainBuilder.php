<?php

namespace Contributte\Middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class ChainBuilder
{

	/** @var array */
	private $middlewares = [];

	/**
	 * @param object $middleware
	 * @return void
	 */
	public function add($middleware)
	{
		$this->middlewares[] = $middleware;
	}

	/**
	 * @return callable
	 */
	public function create()
	{
		$next = function (RequestInterface $request, ResponseInterface $response) {
			return $response;
		};

		$middlewares = $this->middlewares;
		while ($middleware = array_pop($middlewares)) {
			$next = function (RequestInterface $request, ResponseInterface $response) use ($middleware, $next) {
				// Middleware should return ALWAYS reponse!
				return $middleware($request, $response, $next);
			};
		}

		return $next;
	}

}
