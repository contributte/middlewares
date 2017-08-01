<?php

namespace Contributte\Middlewares\UI;

use Contributte\Middlewares\PresenterMiddleware;

interface IPresenterMiddlewareFactory
{

	/**
	 * @return PresenterMiddleware
	 */
	public function create();

}
