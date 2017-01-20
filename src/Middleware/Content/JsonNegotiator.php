<?php

namespace Contributte\Middlewares\Middleware\Content;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JsonNegotiator
{

	/**
	 * @param ServerRequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @param callable $next
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next)
	{
		// Convert content of response to JSON format
		$json = json_encode((string) $psr7Response->getBody());

		// Set JSON data back to response
		$psr7Response->getBody()->rewind();
		$psr7Response->getBody()->write($json);

		// Setup header
		$psr7Response = $psr7Response->withHeader('Content-type', 'application/json');

		return $psr7Response;
	}

}
