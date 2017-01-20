<?php

namespace Contributte\Middlewares\DI;

use Contributte\Middlewares\ChainBuilder;
use Contributte\Middlewares\Exception\InvalidStateException;
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
		$builder->addDefinition($this->prefix('chain'))
			->setClass(ChainBuilder::class);
	}

	/**
	 * Decorate services
	 *
	 * @return void
	 */
	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		// Obtain middleware chain builder
		$chain = $builder->getDefinition($this->prefix('chain'));

		// Add middleware services to chain
		foreach ($config as $service) {
			// Append to chain of middlewares
			$chain->addSetup('add', [$service]);
		}
	}

}
