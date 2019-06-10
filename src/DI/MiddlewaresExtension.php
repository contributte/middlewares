<?php declare(strict_types = 1);

namespace Contributte\Middlewares\DI;

use Contributte\Middlewares\Application\MiddlewareApplication;
use Nette\DI\Definitions\Statement;

class MiddlewaresExtension extends AbstractMiddlewaresExtension
{

	/**
	 * Register middlewares in standalone mode
	 */
	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('application'))
			->setFactory(MiddlewareApplication::class)
			->setArguments([new Statement('@' . $this->prefix('chain') . '::create')]);
	}

}
