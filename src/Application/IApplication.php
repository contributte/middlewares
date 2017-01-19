<?php

namespace Contributte\Middlewares\Application;

use Psr\Http\Message\ResponseInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
interface IApplication
{

	/**
	 * Dispatch application!
	 *
	 * @return ResponseInterface
	 */
	public function run();

}
