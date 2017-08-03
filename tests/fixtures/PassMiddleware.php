<?php

namespace Tests\Fixtures;

use Contributte\Middlewares\IMiddleware;
use Nette\SmartObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PassMiddleware implements IMiddleware
{

	use SmartObject;

	/** @var array */
	public $onBefore = [];

	/** @var array */
	public $onAfter = [];

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param callable $next
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
	{
		$this->onBefore($request, $response);
		$response = $next($request, $response);
		$this->onAfter($request, $response);

		return $response;
	}

}
