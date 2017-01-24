<?php

namespace Contributte\Middlewares\Factory;

use Contributte\Middlewares\Middleware\PresenterMiddleware;

interface IPresenterMiddlewareFactory
{

	/**
	 * @return PresenterMiddleware
	 */
	public function create();

}
