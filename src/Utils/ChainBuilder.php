<?php

namespace Contributte\Middlewares\Utils;

use Contributte\Middlewares\Exception\InvalidStateException;
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
	 * @param mixed $middleware
	 * @return void
	 */
	public function add($middleware)
	{
		if (!is_callable($middleware)) {
			throw new InvalidStateException('Middleware is not callable');
		}

		$this->middlewares[] = $middleware;
	}

	/**
	 * @return callable
	 */
	public function create()
	{
		if (!$this->middlewares) {
			throw new InvalidStateException('Please add at least one middleware');
		}

		$next = Lambda::leaf();

		$middlewares = $this->middlewares;
		while ($middleware = array_pop($middlewares)) {
			$next = function (RequestInterface $request, ResponseInterface $response) use ($middleware, $next) {
				// Middleware should return ALWAYS response!
				return $middleware($request, $response, $next);
			};
		}

		return $next;
	}

}
