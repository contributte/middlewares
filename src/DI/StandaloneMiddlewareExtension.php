<?php

namespace Contributte\Middlewares\DI;

use Contributte\Middlewares\Application\MiddlewareApplication;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;

class StandaloneMiddlewareExtension extends CompilerExtension
{

	/**
	 * Register middlewares in standalone mode
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('application'))
			->setClass(MiddlewareApplication::class)
			->setArguments([new Statement('@' . $this->prefix('chain') . '::create')]);
	}

}
