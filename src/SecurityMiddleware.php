<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Contributte\Middlewares\Security\IAuthenticator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SecurityMiddleware implements IMiddleware
{

	// Attributes in ServerRequestInterface
	public const ATTR_IDENTITY = 'contributte.identity';

	/** @var IAuthenticator */
	private $authenticator;

	public function __construct(IAuthenticator $authenticator)
	{
		$this->authenticator = $authenticator;
	}

	/**
	 * Authenticate user from given request
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next): ResponseInterface
	{
		$identity = $this->authenticator->authenticate($psr7Request);

		// If we have a identity, then go to next middlewares,
		// otherwise stop and return current response
		if (!$identity) {
			return $this->denied($psr7Request, $psr7Response);
		}

		// Add info about current identity
		$psr7Request = $psr7Request->withAttribute(self::ATTR_IDENTITY, $identity);

		// Pass to next middleware
		return $next($psr7Request, $psr7Response);
	}

	protected function denied(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): ResponseInterface
	{
		$psr7Response->getBody()->write((string) json_encode([
			'status' => 'error',
			'message' => 'Client authentication failed',
			'code' => 401,
		]));

		return $psr7Response
			->withHeader('Content-Type', 'application/json')
			->withStatus(401);
	}

}
