<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Tracy;

use Contributte\Middlewares\IMiddleware;
use Tracy\IBarPanel;

class MiddlewaresPanel implements IBarPanel
{

	/** @var CountUsedMiddlewaresMiddleware */
	private $counter;

	/** @var IMiddleware[] */
	private $middlewares;

	public function __construct(CountUsedMiddlewaresMiddleware $counter)
	{
		$this->counter = $counter;
	}

	public function addMiddleware(IMiddleware $middleware): void
	{
		$this->middlewares[] = $middleware;
	}

	public function getTab(): string
	{
		ob_start();
		require __DIR__ . '/templates/tab.phtml';
		return (string) ob_get_clean();
	}

	public function getPanel(): string
	{
		$usedCount = $this->counter->getCount();
		$middlewares = $this->middlewares;

		ob_start();
		require __DIR__ . '/templates/panel.phtml';
		return (string) ob_get_clean();
	}

}
