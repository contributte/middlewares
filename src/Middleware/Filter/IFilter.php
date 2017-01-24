<?php

namespace Contributte\Middlewares\Middleware\Filter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IFilter
{

	/**
	 * @param ServerRequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @param callable $next
	 * @return ResponseInterface|NULL
	 */
	public function filter(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next);

}
