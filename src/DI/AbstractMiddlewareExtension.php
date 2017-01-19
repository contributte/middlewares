<?php

namespace Contributte\Middlewares\DI;

use Contributte\Middleware\Exception\InvalidStateException;
use Contributte\Middlewares\ChainBuilder;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

abstract class AbstractMiddlewareExtension extends CompilerExtension
{

	/**
	 * Register services
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		if (empty($config)) {
			throw new InvalidStateException('There must be at least one middleware registered');
		}

		// Register middleware chain builder
		$chain = $builder->addDefinition($this->prefix('chain'))
			->setClass(ChainBuilder::class);

		// Register middlewares as services
		$counter = 1;
		foreach ($config as $service) {
			// Register single middleware as a service
			$def = $builder->addDefinition($this->prefix('middleware' . $counter++));
			Compiler::loadDefinition($def, $service);
			// Append to chain of middlewares
			$chain->addSetup('add', [$def]);
		}
	}

}
