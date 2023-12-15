<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Security;

use Psr\Http\Message\ServerRequestInterface;

class CompositeAuthenticator implements IAuthenticator
{

	/** @var IAuthenticator[] */
	private array $authenticators = [];

	public function addAuthenticator(IAuthenticator $authenticator): void
	{
		$this->authenticators[] = $authenticator;
	}

	public function authenticate(ServerRequestInterface $request): mixed
	{
		foreach ($this->authenticators as $authenticator) {
			$identity = $authenticator->authenticate($request);
			if ($identity !== null) {
				return $identity;
			}
		}

		return false;
	}

}
