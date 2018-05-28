<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Security;

use Psr\Http\Message\ServerRequestInterface;

class DebugAuthenticator implements IAuthenticator
{

	/** @var mixed */
	private $identity;

	/**
	 * @param mixed $identity
	 */
	public function __construct($identity)
	{
		$this->identity = $identity;
	}

	/**
	 * @return mixed
	 */
	public function authenticate(ServerRequestInterface $request)
	{
		return $this->identity;
	}

}
