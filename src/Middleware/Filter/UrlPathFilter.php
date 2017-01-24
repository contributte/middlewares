<?php

namespace Contributte\Middlewares\Middleware\Filter;

use Contributte\Middlewares\Utils\Regex;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UrlPathFilter implements IFilter
{

	/** @var string */
	private $pattern;

	/**
	 * @param string $pattern
	 */
	public function __construct($pattern)
	{
		$this->pattern = $pattern;
	}

	/**
	 * @param ServerRequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @param callable $next
	 * @return ResponseInterface|NULL
	 */
	public function filter(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next)
	{
		$path = (string) $psr7Request->getUri()->getPath();
		$pattern = sprintf('#%s#', $this->pattern);
		$match = Regex::match($path, $pattern);

		// Return NULL if it's not matched
		if ($match === FALSE) return NULL;

		return $psr7Response;
	}

}
