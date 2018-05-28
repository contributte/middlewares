<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Contributte\Middlewares\AbstractRootMiddleware;
use Contributte\Middlewares\Utils\Lambda;

final class SimpleRootMiddleware extends AbstractRootMiddleware
{

	protected function create(): callable
	{
		return Lambda::leaf();
	}

}
