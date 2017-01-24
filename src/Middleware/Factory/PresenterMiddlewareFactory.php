<?php

namespace Contributte\Middlewares\Factory;

use Contributte\Middlewares\Middleware\PresenterMiddleware;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;

class PresenterMiddlewareFactory implements IPresenterMiddlewareFactory
{

	/** @var IPresenterFactory */
	protected $presenterFactory;

	/** @var IRouter */
	protected $router;

	/**
	 * @param IPresenterFactory $presenterFactory
	 * @param IRouter $router
	 */
	public function __construct(IPresenterFactory $presenterFactory, IRouter $router)
	{
		$this->presenterFactory = $presenterFactory;
		$this->router = $router;
	}

	/**
	 * @return PresenterMiddleware
	 */
	public function create()
	{
		return new PresenterMiddleware($this->presenterFactory, $this->router);
	}

}
