<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IMiddleware
{

	/**
	 * @param callable(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface $next
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface;

}
