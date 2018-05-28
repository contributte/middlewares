<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Contributte\Middlewares\IMiddleware;
use Nette\SmartObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PassMiddleware implements IMiddleware
{

	use SmartObject;

	/** @var callable[] */
	public $onBefore = [];

	/** @var callable[] */
	public $onAfter = [];

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
	{
		$this->onBefore($request, $response);
		$response = $next($request, $response);
		$this->onAfter($request, $response);

		return $response;
	}

}
