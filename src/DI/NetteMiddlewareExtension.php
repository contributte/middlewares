<?php

namespace Contributte\Middlewares\DI;

use Contributte\Middlewares\Application\NetteMiddlewareApplication;
use Nette\DI\ServiceCreationException;
use Nette\DI\Statement;
use Nette\Http\Request;
use Nette\Http\Response;

class NetteMiddlewareExtension extends AbstractMiddlewareExtension
{

	/**
	 * Register middlewares in nette mode
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();

		if (!$builder->getByType(Request::class)) {
			throw new ServiceCreationException(sprintf('Extension needs service %s to be registered', Request::class));
		}

		if (!$builder->getByType(Response::class)) {
			throw new ServiceCreationException(sprintf('Extension needs service %s to be registered', Response::class));
		}

		$builder->addDefinition($this->prefix('application'))
			->setClass(NetteMiddlewareApplication::class)
			->setArguments([new Statement('@' . $this->prefix('chain') . '::create')])
			->addSetup('setHttpRequest', [new Statement('@' . $builder->getByType(Request::class))])
			->addSetup('setHttpResponse', [new Statement('@' . $builder->getByType(Response::class))]);
	}

}
