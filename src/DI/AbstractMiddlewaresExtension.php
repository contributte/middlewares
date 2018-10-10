<?php declare(strict_types = 1);

namespace Contributte\Middlewares\DI;

use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Middlewares\Tracy\DebugChainBuilder;
use Contributte\Middlewares\Tracy\MiddlewaresPanel;
use Contributte\Middlewares\Utils\ChainBuilder;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;
use Nette\PhpGenerator\ClassType;
use Nette\Utils\Validators;

abstract class AbstractMiddlewaresExtension extends CompilerExtension
{

	public const MIDDLEWARE_TAG = 'middleware';

	/** @var mixed[] */
	protected $defaults = [
		'middlewares' => [],
		'debug' => false,
	];

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		Validators::assertField($config, 'middlewares', 'array');

		// Register middleware chain builder
		$chain = $builder->addDefinition($this->prefix('chain'))
			->setAutowired(false);

		if ($config['debug'] !== true) {
			$chain->setFactory(ChainBuilder::class);
		} else {
			$chain->setFactory(DebugChainBuilder::class);

			$builder->addDefinition($this->prefix('middlewaresPanel'))
				->setFactory(MiddlewaresPanel::class, [$chain]);
		}
	}

	/**
	 * Decorate services
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		// Compile defined middlewares
		if ($config['middlewares'] !== []) {
			$this->compileDefinedMiddlewares();

			return;
		}

		// Compile tagged middlewares
		if ($builder->findByTag(self::MIDDLEWARE_TAG) !== []) {
			$this->compileTaggedMiddlewares();

			return;
		}

		throw new InvalidStateException('There must be at least one middleware registered or added by tag.');
	}

	private function compileDefinedMiddlewares(): void
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

	private function compileTaggedMiddlewares(): void
	{
		$builder = $this->getContainerBuilder();

		// Find all definitions by tag
		$definitions = $builder->findByTag(self::MIDDLEWARE_TAG);

		// Ensure we have at least 1 service
		if ($definitions === []) {
			throw new InvalidStateException(sprintf('No services with tag "%s"', self::MIDDLEWARE_TAG));
		}

		// Sort by priority
		uasort($definitions, function ($a, $b) {
			$p1 = $a['priority'] ?? 10;
			$p2 = $b['priority'] ?? 10;

			if ($p1 === $p2) {
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

	public function afterCompile(ClassType $class): void
	{
		$config = $this->validateConfig($this->defaults);

		if ($config['debug'] === true) {
			$initialize = $class->getMethod('initialize');
			$initialize->addBody(
				'$this->getService(?)->addPanel($this->getService(?));',
				['tracy.bar', $this->prefix('middlewaresPanel')]
			);
		}
	}

}
