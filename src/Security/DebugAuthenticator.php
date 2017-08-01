<?php

namespace Contributte\Middlewares\Security;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
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
	 * @param ServerRequestInterface $request
	 * @return mixed
	 */
	public function authenticate(ServerRequestInterface $request)
	{
		return $this->identity;
	}

}
