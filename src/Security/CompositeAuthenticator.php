<?php

namespace Contributte\Middlewares\Security;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class CompositeAuthenticator implements IAuthenticator
{

	/** @var IAuthenticator[] */
	private $authenticators = [];

	/**
	 * @param IAuthenticator $authenticator
	 * @return void
	 */
	public function addAuthenticator(IAuthenticator $authenticator)
	{
		$this->authenticators[] = $authenticator;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @return mixed
	 */
	public function authenticate(ServerRequestInterface $request)
	{
		foreach ($this->authenticators as $authenticator) {
			$identity = $authenticator->authenticate($request);
			if ($identity) return $identity;
		}

		return FALSE;
	}

}
