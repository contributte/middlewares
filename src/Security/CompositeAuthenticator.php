<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Security;

use Psr\Http\Message\ServerRequestInterface;

class CompositeAuthenticator implements IAuthenticator
{

	/** @var IAuthenticator[] */
	private $authenticators = [];

	public function addAuthenticator(IAuthenticator $authenticator): void
	{
		$this->authenticators[] = $authenticator;
	}

	/**
	 * @return mixed
	 */
	public function authenticate(ServerRequestInterface $request)
	{
		foreach ($this->authenticators as $authenticator) {
			$identity = $authenticator->authenticate($request);
			if ($identity) {
				return $identity;
			}
		}

		return false;
	}

}
