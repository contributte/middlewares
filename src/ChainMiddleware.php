<?php

namespace Contributte\Middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class ChainMiddleware implements IMiddleware
{

	/** @var callable|IMiddleware => callable|IMiddleware */
	private $chain;

	/**
	 * @param callable|IMiddleware $chain
	 */
	public function __construct($chain)
	{
		$this->chain = $chain;
	}

	/**
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 * @param callable $next
	 * @return ResponseInterface
	 */
	public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
	{
		return call_user_func($this->chain, $request, $response);
	}

}
