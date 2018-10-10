<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Tracy;

use Tracy\IBarPanel;

class MiddlewaresPanel implements IBarPanel
{

	/** @var DebugChainBuilder */
	private $chainBuilder;

	public function __construct(DebugChainBuilder $chainBuilder)
	{
		$this->chainBuilder = $chainBuilder;
	}

	public function getTab(): string
	{
		ob_start();
		require __DIR__ . '/templates/tab.phtml';
		return (string) ob_get_clean();
	}

	public function getPanel(): string
	{
		$usedCount = $this->chainBuilder->getUsedCount();
		$middlewares = $this->chainBuilder->getAll();

		ob_start();
		require __DIR__ . '/templates/panel.phtml';
		return (string) ob_get_clean();
	}

}
