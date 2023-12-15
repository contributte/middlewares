<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Closure;
use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\Response;

class PresenterFactory implements IPresenterFactory
{

	/** @var array<IPresenter> */
	private array $presenters = [];

	/**
	 * @param array<string, Closure> $closures
	 */
	public function __construct(array $closures)
	{
		foreach ($closures as $name => $closure) {
			$this->presenters[$name] = new class($closure) implements IPresenter {

				private Closure $closure;

				public function __construct(Closure $closure)
				{
					$this->closure = $closure;
				}

				public function run(Request $request): Response
				{
					$c = $this->closure;

					return $c($request);
				}

			};
		}
	}

	/**
	 * @phpcs:disable SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
	 */
	public function getPresenterClass(string &$name): string
	{
		return '';
	}

	public function createPresenter(string $name): IPresenter
	{
		return $this->presenters[$name];
	}

}
