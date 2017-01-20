<?php

namespace Contributte\Middlewares\Middleware\Filter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UrlPathFilter
{

	/** @var string */
	private $mask;

	/**
	 * @param string $mask
	 */
	public function __construct($mask)
	{
		$this->mask = $mask;
	}

	/**
	 * @param ServerRequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @param callable $next
	 * @return mixed
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next)
	{
		$ret = preg_match(sprintf('#%s#', $this->mask), (string) $psr7Request->getUri()->getPath(), $matches);

		// Return FALSE if is not matched
		if ($ret !== 1) return FALSE;

		return $psr7Request;
	}

}
