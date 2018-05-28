<?php declare(strict_types = 1);

namespace Contributte\Middlewares\DI;

use Contributte\Middlewares\Application\NetteMiddlewareApplication;
use Nette\DI\ServiceCreationException;
use Nette\DI\Statement;
use Nette\Http\Request;
use Nette\Http\Response;

class NetteMiddlewaresExtension extends AbstractMiddlewaresExtension
{

	/**
	 * Register middlewares in nette mode
	 */
	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		if ($builder->getByType(Request::class) === null) {
			throw new ServiceCreationException(sprintf('Extension needs service %s. Do you have nette/http in composer file?', Request::class));
		}

		if ($builder->getByType(Response::class) === null) {
			throw new ServiceCreationException(sprintf('Extension needs service %s. Do you have nette/http in composer file?', Response::class));
		}

		$application = $builder->addDefinition($this->prefix('application'))
			->setClass(NetteMiddlewareApplication::class);

		$application->addSetup('setHttpRequest', [new Statement('@' . $builder->getByType(Request::class))])
			->addSetup('setHttpResponse', [new Statement('@' . $builder->getByType(Response::class))]);

		if ($config['root'] !== null) {
			$application->setArguments([new Statement($config['root'])]);
		} else {
			$application->setArguments([new Statement('@' . $this->prefix('chain') . '::create')]);
		}
	}

}
