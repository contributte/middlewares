<?php declare(strict_types = 1);

namespace Contributte\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Drop base path from URL by auto-detection
 */
class AutoBasePathMiddleware implements IMiddleware
{

	// Attributes in ServerRequestInterface
	public const
		ATTR_ORIGINAL_PATH = 'contributte.original.path',
		ATTR_BASE_PATH = 'contributte.base.path',
		ATTR_PATH = 'contributte.path';

	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next): ResponseInterface
	{
		$uri = $psr7Request->getUri();
		$basePath = $uri->getPath();

		// Base-path auto detection (inspired in @nette/routing)
		$lpath = strtolower($uri->getPath());
		$serverParams = $psr7Request->getServerParams();

		$script = isset($serverParams['SCRIPT_NAME']) ? strtolower($serverParams['SCRIPT_NAME']) : '';
		if ($lpath !== $script) {
			$max = min(strlen($lpath), strlen($script));
			$i = 0;
			while ($i < $max && $lpath[$i] === $script[$i]) {
				$i++;
			}

			// Cut basePath from URL
			// /foo/bar/test => /test
			// (empty) -> /
			$basePath = $i !== 0 ? substr($basePath, 0, (int) strrpos($basePath, '/', $i - strlen($basePath) - 1) + 1) : '/';
		}

		// Try replace path or just use slash (/)
		$pos = strrpos($basePath, '/');
		if ($pos !== false) {
			// Cut base path by last slash (/)
			$basePath = substr($basePath, 0, $pos + 1);
			// Drop part of path (basePath)
			$newPath = substr($uri->getPath(), strlen($basePath));
		} else {
			$newPath = '/';
		}

		// New path always starts with slash (/)
		$newPath = '/' . ltrim($newPath, '/');

		// Update request with new path (fake path) and also provide new attributes
		$psr7Request = $psr7Request
			->withAttribute(self::ATTR_ORIGINAL_PATH, $uri->getPath())
			->withAttribute(self::ATTR_BASE_PATH, $basePath)
			->withAttribute(self::ATTR_PATH, $newPath)
			->withUri($uri->withPath($newPath));

		// Pass to next middleware
		return $next($psr7Request, $psr7Response);
	}

}
