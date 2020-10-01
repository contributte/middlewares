<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Utils;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Lambda
{

	public static function blank(): callable
	{
		return function (): void {
			// Empty function
		};
	}

	public static function leaf(): callable
	{
		return function (RequestInterface $request, ResponseInterface $response): ResponseInterface {
			return $response;
		};
	}

}
