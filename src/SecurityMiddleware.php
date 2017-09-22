<?php

namespace Contributte\Middlewares;

use Contributte\Middlewares\Security\IAuthenticator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class SecurityMiddleware extends BaseMiddleware
{

	// Attributes in ServerRequestInterface
	const ATTR_IDENTITY = 'contributte.identity';

	/** @var IAuthenticator */
	private $authenticator;

	/**
	 * @param IAuthenticator $authenticator
	 */
	public function __construct(IAuthenticator $authenticator)
	{
		$this->authenticator = $authenticator;
	}

	/**
	 * Drop base path from URL
	 *
	 * @param ServerRequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @param callable $next
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next)
	{
		$identity = $this->authenticator->authenticate($psr7Request);

		// If we have a identity, then go to next middlewares,
		// otherwise stop and return current response
		if (!$identity) return $psr7Response;

		// Add info about current identity
		$psr7Request = $psr7Request->withAttribute(self::ATTR_IDENTITY, $identity);

		// Pass to next middleware
		return $next($psr7Request, $psr7Response);
	}

}
