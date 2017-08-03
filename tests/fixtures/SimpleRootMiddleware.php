<?php

namespace Tests\Fixtures;

use Contributte\Middlewares\AbstractRootMiddleware;
use Contributte\Middlewares\Utils\Lambda;

final class SimpleRootMiddleware extends AbstractRootMiddleware
{

	/**
	 * @return callable
	 */
	protected function create()
	{
		return Lambda::leaf();
	}

}
