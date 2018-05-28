<?php declare(strict_types = 1);

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

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
	{
		foreach ($this->inner as $middleware) {
			$response = $middleware($request, $response, Lambda::leaf());
		}

		return $response;
	}

}
