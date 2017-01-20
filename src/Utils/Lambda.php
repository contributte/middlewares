<?php

namespace Contributte\Middlewares\Utils;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Lambda
{

	/**
	 * @return callable
	 */
	public static function blank()
	{
		return function () {
			// Empty function
		};
	}

	/**
	 * @return callable
	 */
	public static function leaf()
	{
		return function (RequestInterface $request, ResponseInterface $response) {
			return $response;
		};
	}

}
