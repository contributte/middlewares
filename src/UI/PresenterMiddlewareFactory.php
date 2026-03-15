<?php declare(strict_types = 1);

namespace Contributte\Middlewares\UI;

use Contributte\Middlewares\PresenterMiddleware;
use Nette\Application\IPresenterFactory;
use Nette\Routing\Router;

class PresenterMiddlewareFactory implements IPresenterMiddlewareFactory
{

	public function __construct(
		protected IPresenterFactory $presenterFactory,
		protected Router $router,
	)
	{
	}

	public function create(): PresenterMiddleware
	{
		return new PresenterMiddleware($this->presenterFactory, $this->router);
	}

}
