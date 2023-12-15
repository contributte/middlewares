<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use Nette\SmartObject;

final class MutableExtension extends CompilerExtension
{

	use SmartObject;

	/** @var callable[] */
	public array $onLoad = [];

	/** @var callable[] */
	public array $onBefore = [];

	/** @var callable[] */
	public array $onAfter = [];

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		$this->onLoad($this, $this->getContainerBuilder(), $this->getConfig());
	}

	/**
	 * Decorate services
	 */
	public function beforeCompile(): void
	{
		$this->onBefore($this, $this->getContainerBuilder(), $this->getConfig());
	}

	public function afterCompile(ClassType $class): void
	{
		$this->onAfter($this, $class, $this->getConfig());
	}

}
