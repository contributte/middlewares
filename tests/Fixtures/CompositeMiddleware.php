<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Contributte\Middlewares\IMiddleware;
use Contributte\Middlewares\Utils\Lambda;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CompositeMiddleware implements IMiddleware
{

	/**
	 * @param IMiddleware[] $inner
	 */
	public function __construct(
		private readonly array $inner,
	)
	{
	}

	/**
	 * @param callable(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface $next
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
	{
		foreach ($this->inner as $middleware) {
			$response = $middleware($request, $response, Lambda::leaf());
		}

		return $response;
	}

}
