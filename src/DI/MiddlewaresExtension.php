<?php declare(strict_types = 1);

namespace Contributte\Middlewares\DI;

use Contributte\Middlewares\Application\MiddlewareApplication;
use Nette\DI\Statement;

class MiddlewaresExtension extends AbstractMiddlewaresExtension
{

	/**
	 * Register middlewares in standalone mode
	 */
	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$application = $builder->addDefinition($this->prefix('application'))
			->setClass(MiddlewareApplication::class)
			->setArguments([$this->prefix('chain')]);

		if ($config['root'] !== null) {
			$application->setArguments([new Statement($config['root'])]);
		} else {
			$application->setArguments([new Statement('@' . $this->prefix('chain') . '::create')]);
		}
	}

}
