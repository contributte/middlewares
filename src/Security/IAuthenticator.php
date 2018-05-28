<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Security;

use Psr\Http\Message\ServerRequestInterface;

interface IAuthenticator
{

	/**
	 * @return mixed
	 */
	public function authenticate(ServerRequestInterface $request);

}
