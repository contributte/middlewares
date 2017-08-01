<?php

namespace Contributte\Middlewares\DI;

use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Middlewares\Utils\ChainBuilder;
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

		if ($config['root'] !== NULL) {
			if (strncmp($config['root'], '@', 1) !== 0) {
				throw new InvalidStateException('Pass root middleware as a service with @ at the beginning');
			}
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
		foreach ($config['middlewares'] as $service) {
			// Append to chain of middlewares
			$chain->addSetup('add', [$service]);
		}
	}

}
