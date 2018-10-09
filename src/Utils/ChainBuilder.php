<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Utils;

use Contributte\Middlewares\Exception\InvalidStateException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ChainBuilder
{

	/** @var mixed[] */
	private $middlewares = [];

	public function add(callable $middleware): void
	{
		$this->middlewares[] = $middleware;
	}

	/**
	 * @param mixed[] $middlewares
	 */
	public function addAll(array $middlewares): void
	{
		foreach ($middlewares as $middleware) {
			$this->add($middleware);
		}
	}

	public function create(): callable
	{
		if ($this->middlewares === []) {
			throw new InvalidStateException('At least one middleware is needed');
		}

		$next = Lambda::leaf();

		$middlewares = $this->middlewares;
		while ($middleware = array_pop($middlewares)) {
			$next = function (RequestInterface $request, ResponseInterface $response) use ($middleware, $next): ResponseInterface {
				return $middleware($request, $response, $next);
			};
		}

		return $next;
	}

	/**
	 * @param mixed[] $middlewares
	 */
	public static function factory(array $middlewares): callable
	{
		$chain = new ChainBuilder();
		$chain->addAll($middlewares);

		return $chain->create();
	}

}
