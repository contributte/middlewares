<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BasePathMiddleware implements IMiddleware
{

	private readonly string $basePath;

	public function __construct(string $basePath)
	{
		$this->basePath = '/' . ltrim($basePath, '/');
	}

	/**
	 * Drop base path from URL
	 *
	 * @param callable(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface $next
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next): ResponseInterface
	{
		$uri = $psr7Request->getUri();

		// Does URL path start with given base path?
		// Otherwise skip to next middleware
		if (str_starts_with($uri->getPath(), $this->basePath)) {
			$newPath = '/' . substr($uri->getPath(), strlen($this->basePath));
			$psr7Request = $psr7Request->withUri($uri->withPath($newPath));
		}

		// Pass to next middleware
		return $next($psr7Request, $psr7Response);
	}

}
