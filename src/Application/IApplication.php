<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Application;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IApplication
{

	/**
	 * Dispatch application!
	 *
	 * @return string|int|bool|void|ResponseInterface|null
	 */
	public function run();

	/**
	 * Dispatch application!
	 *
	 * @return string|int|bool|void|ResponseInterface|null
	 */
	public function runWith(ServerRequestInterface $request);

}
