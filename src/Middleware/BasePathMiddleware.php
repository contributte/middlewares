<?php

namespace Contributte\Middlewares\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class BasePathMiddleware extends BaseMiddleware
{

	/** @var string */
	private $basePath;

	/**
	 * @param string $basePath
	 */
	public function __construct($basePath)
	{
		$this->basePath = '/' . ltrim($basePath, '/');
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
		$uri = $psr7Request->getUri();

		// Does URL path start with given base path?
		// Otherwise skip to next middleware
		if (strncmp($uri->getPath(), $this->basePath, strlen($this->basePath)) === 0) {
			$newPath = str_replace($this->basePath, NULL, $uri->getPath());
			$psr7Request = $psr7Request->withUri($uri->withPath($newPath));
		}

		// Pass to next middleware
		return $next($psr7Request, $psr7Response);
	}

}
