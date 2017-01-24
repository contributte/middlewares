<?php

namespace Contributte\Middlewares\Middleware;

use Contributte\Middlewares\Middleware\Filter\IFilter;
use Contributte\Middlewares\Utils\Lambda;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class ExcludeConditionMiddleware extends BaseMiddleware
{

	/** @var IFilter */
	private $filter;

	/** @var callable */
	private $middleware;

	/**
	 * @param IFilter $filter
	 * @param callable $middleware
	 */
	public function __construct(IFilter $filter, $middleware)
	{
		$this->filter = $filter;
		$this->middleware = $middleware;
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
		$retval = $this->filter->filter($psr7Request, $psr7Response, Lambda::blank());

		if ($retval === NULL) {
			// Condition is not applied, pass to next middleware immediately.
			return $next($psr7Request, $psr7Response);
		}

		// Process inner chain of middlewares
		$psr7Response = call_user_func_array($this->middleware, [$psr7Request, $psr7Response, Lambda::leaf()]);

		// This is exclude condition, if it's matched, do not pass to next middleware
		return $psr7Response;
	}

}
