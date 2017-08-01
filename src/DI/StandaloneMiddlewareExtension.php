<?php

namespace Contributte\Middlewares\DI;

use Contributte\Middlewares\Application\MiddlewareApplication;
use Nette\DI\Statement;

class StandaloneMiddlewareExtension extends AbstractMiddlewareExtension
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
		$config = $this->validateConfig($this->defaults);

		$application = $builder->addDefinition($this->prefix('application'))
			->setClass(MiddlewareApplication::class)
			->setArguments([$this->prefix('chain')]);

		if ($config['root'] !== NULL) {
			$application->setArguments([new Statement($config['root'])]);
		} else {
			$application->setArguments([new Statement('@' . $this->prefix('chain') . '::create')]);
		}
	}

}
