<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Application;

use Psr\Http\Message\ResponseInterface;

interface IApplication
{

	/**
	 * Dispatch application!
	 *
	 * @return string|int|null|bool|void|ResponseInterface
	 */
	public function run();

}
