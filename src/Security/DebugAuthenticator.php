<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Security;

use Psr\Http\Message\ServerRequestInterface;

class DebugAuthenticator implements IAuthenticator
{

	private mixed $identity;

	public function __construct(mixed $identity)
	{
		$this->identity = $identity;
	}

	public function authenticate(ServerRequestInterface $request): mixed
	{
		return $this->identity;
	}

}
