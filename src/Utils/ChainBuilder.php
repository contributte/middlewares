<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Utils;

use Contributte\Middlewares\Exception\InvalidStateException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ChainBuilder
{

	/** @var array<callable> */
	protected array $middlewares = [];

	/**
	 * @param array<callable> $middlewares
	 */
	public static function factory(array $middlewares): callable
	{
		$chain = new ChainBuilder();
		$chain->addAll($middlewares);

		return $chain->create();
	}

	public function add(callable $middleware): void
	{
		$this->middlewares[] = $middleware;
	}

	/**
	 * @param array<callable> $middlewares
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
			$next = fn (RequestInterface $request, ResponseInterface $response): ResponseInterface => $middleware($request, $response, $next);
		}

		return $next;
	}

}
