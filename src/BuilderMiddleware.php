<?php

namespace Contributte\Middlewares;

use Contributte\Middlewares\Utils\ChainBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class BuilderMiddleware extends BaseMiddleware
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
	 * @return void
	 */
	public function add($middleware)
	{
		$this->builder->add($middleware);
	}

	/**
	 * @return callable
	 */
	protected function create()
	{
		return $this->builder->create();
	}

	/**
	 * @param ServerRequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @param callable $next
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next)
	{
		// Create chain of middlewares
		$chain = $this->create();

		// Process inner chain of middlewares
		$psr7Response = call_user_func_array($chain, [$psr7Request, $psr7Response]);

		// Pass to next middleware
		return $next($psr7Request, $psr7Response);
	}

}
