<?php

namespace Contributte\Middlewares\Middleware;

use Contributte\Middlewares\Utils\Lambda;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class ContentNegotiationMiddleware extends BaseMiddleware
{

	/** @var callable[] */
	private $strategies;

	/**
	 * @param callable $strategies
	 */
	public function __construct(array $strategies)
	{
		$this->strategies = $strategies;
	}

	/**
	 * @param ServerRequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @param callable $next
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next)
	{
		// Pass to next middleware
		$psr7Response = $next($psr7Request, $psr7Response);

		return $this->negotiate($psr7Request, $psr7Response);
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 */
	protected function negotiate(ServerRequestInterface $request, ResponseInterface $response)
	{
		foreach ($this->strategies as $strategy) {
			$ret = $strategy($request, $response, Lambda::leaf());

			// Skip to next strategy
			if ($ret === NULL) continue;

			return $ret;
		}

		return $response;
	}

}
