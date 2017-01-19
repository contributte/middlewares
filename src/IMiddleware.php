<?php
namespace Contributte\Middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
interface IMiddleware
{

	/**
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 * @param callable $next
	 * @return ResponseInterface
	 */
	public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next);

}
