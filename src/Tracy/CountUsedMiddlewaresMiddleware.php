<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Tracy;

use Contributte\Middlewares\IMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CountUsedMiddlewaresMiddleware implements IMiddleware
{

	/** @var int */
	private $count = 0;

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
	{
		$this->count++;
		return $next($request, $response);
	}

	public function getCount(): int
	{
		return $this->count;
	}

}
