<?php

namespace Tests\Fixtures;

use Contributte\Middlewares\IMiddleware;
use Contributte\Middlewares\Utils\Lambda;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CompositeMiddleware implements IMiddleware
{

	/** @var IMiddleware[] */
	private $inner = [];

	/**
	 * @param IMiddleware[] $inner
	 */
	public function __construct(array $inner)
	{
		$this->inner = $inner;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param callable $next
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
	{
		foreach ($this->inner as $middleware) {
			$response = $middleware($request, $response, Lambda::leaf());
		}

		return $response;
	}

}
