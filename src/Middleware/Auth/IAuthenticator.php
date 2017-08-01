<?php

namespace Contributte\Middlewares\Middleware\Security;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
interface IAuthenticator
{

	/**
	 * @param ServerRequestInterface $request
	 * @return mixed
	 */
	public function authenticate(ServerRequestInterface $request);

}
