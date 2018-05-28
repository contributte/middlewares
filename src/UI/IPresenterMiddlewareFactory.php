<?php declare(strict_types = 1);

namespace Contributte\Middlewares\UI;

use Contributte\Middlewares\PresenterMiddleware;

interface IPresenterMiddlewareFactory
{

	public function create(): PresenterMiddleware;

}
