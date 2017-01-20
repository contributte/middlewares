<?php

namespace Contributte\Middlewares\Middleware;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class GroupMiddleware extends GroupBuilderMiddleware
{

	/**
	 * @param callable[] $middlewares
	 */
	public function __construct(array $middlewares)
	{
		parent::__construct();

		foreach ($middlewares as $middleware) {
			$this->add($middleware);
		}
	}

}
