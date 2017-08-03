<?php

namespace Contributte\Middlewares\DI;

use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Middlewares\Utils\ChainBuilder;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\Utils\Validators;

abstract class AbstractMiddlewareExtension extends CompilerExtension
{

	/** @var array */
	protected $defaults = [
		'middlewares' => [],
		'root' => NULL,
	];

	/**
	 * Register services
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		Validators::assertField($config, 'middlewares', 'array');
		Validators::assertField($config, 'root', 'string|null');

		if (empty($config['middlewares']) && $config['root'] === NULL) {
			throw new InvalidStateException('There must be at least one middleware registered or root middleware configured.');
		}

		// Skip next registration, if root middleware is specified
		if ($config['root'] !== NULL) return;

		// Register middleware chain builder
		$builder->addDefinition($this->prefix('chain'))
			->setClass(ChainBuilder::class)
			->setAutowired(FALSE);
	}

	/**
	 * Decorate services
	 *
	 * @return void
	 */
	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		// Skip next registration, if root middleware is specified
		if ($config['root'] !== NULL) return;

		// Obtain middleware chain builder
		$chain = $builder->getDefinition($this->prefix('chain'));

		// Add middleware services to chain
		$counter = 0;
		foreach ($config['middlewares'] as $service) {

			// Create middleware as service
			if (strncmp($service, '@', 1) !== 0) {
				$def = $builder->addDefinition($this->prefix('middleware' . ($counter++)));
				Compiler::loadDefinition($def, $service);
			} else {
				$def = $builder->getDefinition(ltrim($service, '@'));
			}

			$def->addTag('middleware');

			// Append to chain of middlewares
			$chain->addSetup('add', [$def]);
		}
	}

}
