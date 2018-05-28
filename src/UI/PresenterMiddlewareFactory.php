<?php declare(strict_types = 1);

namespace Contributte\Middlewares\UI;

use Contributte\Middlewares\PresenterMiddleware;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;

class PresenterMiddlewareFactory implements IPresenterMiddlewareFactory
{

	/** @var IPresenterFactory */
	protected $presenterFactory;

	/** @var IRouter */
	protected $router;

	public function __construct(IPresenterFactory $presenterFactory, IRouter $router)
	{
		$this->presenterFactory = $presenterFactory;
		$this->router = $router;
	}

	public function create(): PresenterMiddleware
	{
		return new PresenterMiddleware($this->presenterFactory, $this->router);
	}

}
