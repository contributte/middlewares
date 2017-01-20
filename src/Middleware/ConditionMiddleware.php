<?php

namespace Contributte\Middlewares\Middleware;

use Contributte\Middlewares\IMiddleware;
use Contributte\Middlewares\Utils\Lambda;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class ConditionMiddleware extends BaseMiddleware
{

	/** @var callable|IMiddleware */
	private $filter;

	/** @var callable|GroupBuilderMiddleware */
	private $group;

	/**
	 * @param callable|IMiddleware $filter
	 * @param callable|IMiddleware $group
	 */
	public function __construct($filter, $group)
	{
		$this->filter = $filter;
		$this->group = $group;
	}

	/**
	 * @param ServerRequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @param callable $next
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next)
	{
		// Pass to filter middleware
		$retval = call_user_func_array($this->filter, [$psr7Request, $psr7Response, Lambda::blank()]);

		if ($retval === FALSE) {
			// Condition is not applied, pass to next middleware immediately.
			return $next($psr7Request, $psr7Response);
		}

		// Process inner chain of middlewares
		$psr7Response = call_user_func_array($this->group, [$psr7Request, $psr7Response, Lambda::leaf()]);

		// Return response, don't pass to next middleware
		return $psr7Response;
	}

}
