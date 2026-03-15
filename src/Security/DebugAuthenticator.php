<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Security;

use Psr\Http\Message\ServerRequestInterface;

class DebugAuthenticator implements IAuthenticator
{

	public function __construct(
		private readonly mixed $identity,
	)
	{
	}

	public function authenticate(ServerRequestInterface $request): mixed
	{
		return $this->identity;
	}

}
