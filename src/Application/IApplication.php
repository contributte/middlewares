<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Application;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IApplication
{

	/**
	 * Dispatch application!
	 */
	public function run(): ResponseInterface;

	/**
	 * Dispatch application!
	 */
	public function runWith(ServerRequestInterface $request): ResponseInterface;

}
