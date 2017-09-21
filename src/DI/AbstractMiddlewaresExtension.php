<?php

namespace Contributte\Middlewares\DI;

use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Middlewares\Utils\ChainBuilder;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;
use Nette\Utils\Validators;

abstract class AbstractMiddlewaresExtension extends CompilerExtension
{

	const MIDDLEWARE_TAG = 'middleware';

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
		$config = $this->getConfig();

		// Skip next registration, if root middleware is specified
		if ($config['root'] !== NULL) {
			return;
		}

		// Compile defined middlewares
		if (!empty($config['middlewares'])) {
			$this->compileDefinedMiddlewares();

			return;
		}

		// Compile tagged middlewares
		if ($builder->findByTag(self::MIDDLEWARE_TAG)) {
			$this->compileTaggedMiddlewares();

			return;
		}

		throw new InvalidStateException('There must be at least one middleware registered, tag middleware added or root middleware configured.');
	}

	/**
	 * @return void
	 */
	private function compileDefinedMiddlewares()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		// Obtain middleware chain builder
		$chain = $builder->getDefinition($this->prefix('chain'));

		// Add middleware services to chain
		$counter = 0;
		foreach ($config['middlewares'] as $service) {

			// Create middleware as service
			if (
				is_array($service)
				|| $service instanceof Statement
				|| (is_string($service) && strncmp($service, '@', 1) !== 0)
			) {
				$def = $builder->addDefinition($this->prefix('middleware' . ($counter++)));
				Compiler::loadDefinition($def, $service);
			} else {
				$def = $builder->getDefinition(ltrim($service, '@'));
			}

			// Append to chain of middlewares
			$chain->addSetup('add', [$def]);
		}
	}

	/**
	 * @return void
	 */
	private function compileTaggedMiddlewares()
	{
		$builder = $this->getContainerBuilder();

		// Find all definitions by tag
		$definitions = $builder->findByTag(self::MIDDLEWARE_TAG);

		// Ensure we have at least 1 service
		if (!$definitions) {
			throw new InvalidStateException(sprintf('No services with tag "%s"', self::MIDDLEWARE_TAG));
		}

		// Sort by priority
		uasort($definitions, function ($a, $b) {
			$p1 = isset($a['priority']) ? $a['priority'] : 10;
			$p2 = isset($b['priority']) ? $b['priority'] : 10;

			if ($p1 == $p2) {
				return 0;
			}

			return ($p1 < $p2) ? -1 : 1;
		});

		// Obtain middleware chain builder
		$chain = $builder->getDefinition($this->prefix('chain'));

		// Add middleware services to chain
		foreach ($definitions as $name => $tag) {
			// Append to chain of middlewares
			$chain->addSetup('add', [$builder->getDefinition($name)]);
		}
	}

}
