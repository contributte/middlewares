<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Contributte\Middlewares\Utils\ChainBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BuilderMiddleware implements IMiddleware
{

	/** @var ChainBuilder */
	private $builder;

	/**
	 * Creates middleware
	 */
	public function __construct()
	{
		$this->builder = new ChainBuilder();
	}

	/**
	 * @param mixed $middleware
	 */
	public function add($middleware): void
	{
		$this->builder->add($middleware);
	}

	protected function create(): callable
	{
		return $this->builder->create();
	}

	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next): ResponseInterface
	{
		// Create chain of middlewares
		$chain = $this->create();

		// Process inner chain of middlewares
		$psr7Response = call_user_func_array($chain, [$psr7Request, $psr7Response]);

		// Pass to next middleware
		return $next($psr7Request, $psr7Response);
	}

}
