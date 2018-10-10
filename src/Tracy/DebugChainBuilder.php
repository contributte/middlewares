<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Tracy;

use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Middlewares\Utils\ChainBuilder;
use Contributte\Middlewares\Utils\Lambda;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DebugChainBuilder extends ChainBuilder
{

	/** @var int */
	private $usedCount = 0;

	public function create(): callable
	{
		if ($this->middlewares === []) {
			throw new InvalidStateException('At least one middleware is needed');
		}

		$next = Lambda::leaf();

		$middlewares = $this->middlewares;
		while ($middleware = array_pop($middlewares)) {
			$next = function (RequestInterface $request, ResponseInterface $response) use ($middleware, $next): ResponseInterface {
				$this->usedCount++;
				return $middleware($request, $response, $next);
			};
		}

		return $next;
	}

	public function getUsedCount(): int
	{
		return $this->usedCount;
	}

	/**
	 * @return callable[]
	 */
	public function getAll(): array
	{
		return $this->middlewares;
	}

}
