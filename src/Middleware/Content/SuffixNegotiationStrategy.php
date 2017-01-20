<?php

namespace Contributte\Middlewares\Middleware\Content;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SuffixNegotiationStrategy
{

	/** @var callable[] */
	private $negotiators = [];

	/**
	 * @param callable[] $negotiators
	 */
	public function __construct(array $negotiators = [])
	{
		$this->negotiators = $negotiators;
	}

	/**
	 * @param string $suffix
	 * @param callable $negotiator
	 * @return void
	 */
	public function add($suffix, $negotiator)
	{
		$this->negotiators[$suffix] = $negotiator;
	}

	/**
	 * @param ServerRequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @param callable $next
	 * @return mixed
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next)
	{
		$url = $psr7Request->getUri()->getPath();

		foreach ($this->negotiators as $suffix => $negotiator) {
			// Can we apply negotiator?
			if (substr($url, -strlen($suffix)) === $suffix) {
				return $negotiator($psr7Request, $psr7Response, $next);
			}
		}

		// No negotiator applied, just return response
		return $psr7Response;
	}

}
