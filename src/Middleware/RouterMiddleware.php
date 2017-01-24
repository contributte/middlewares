<?php

namespace Contributte\Middlewares\Middleware;

use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Middlewares\Utils\Lambda;
use Contributte\Middlewares\Utils\Regex;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class RouterMiddleware extends BaseMiddleware
{

	// Attributes in ServerRequestInterface
	const ATTR_MATCHED_PATTERN = 'C-Router-Pattern';
	const ATTR_MATCHED_REGEX = 'C-Router-Regex';
	const ATTR_MATCHED_PATH = 'C-Router-Path';
	const ATTR_MATCHED_ATTR = 'C-Router-Attr';

	/** @var array */
	private $routes;

	/** @var array */
	private $compiled;

	/**
	 * @param array $routes
	 */
	public function __construct(array $routes)
	{
		$this->routes = $routes;
	}

	/**
	 * @param ServerRequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @param callable $next
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response, callable $next)
	{
		// Compile routes patterns
		$this->compiled = $this->compile($this->routes);

		// Try to match request to routes
		$psr7Response = $this->match($psr7Request, $psr7Response);

		// Pass to next middleware
		return $next($psr7Request, $psr7Response);
	}

	/**
	 * @param ServerRequestInterface $psr7Request
	 * @param ResponseInterface $psr7Response
	 * @return ResponseInterface
	 */
	protected function match(ServerRequestInterface $psr7Request, ResponseInterface $psr7Response)
	{
		$path = $psr7Request->getUri()->getPath();

		// Iterate over compiled routes
		foreach ($this->compiled as $route) {
			// Try match pattern againts to URL path
			// @idea: support callback matcher?
			$match = Regex::match($path, $route['regex']);

			// We have a match! Yeah.
			if ($match !== NULL) {
				// Append router specific attributes to request
				$psr7Request = $psr7Request
					->withAttribute(self::ATTR_MATCHED_PATTERN, $route['pattern'])
					->withAttribute(self::ATTR_MATCHED_REGEX, $route['regex'])
					->withAttribute(self::ATTR_MATCHED_PATH, $path);

				foreach ($route['variables'] as $variable) {
					$psr7Request = $psr7Request
						->withAttribute(sprintf('%s-%s', self::ATTR_MATCHED_ATTR, $variable), $match[$variable]);
				}

				// Process subset of middlewares, according to matched route
				$psr7Response = call_user_func_array($route['middleware'], [$psr7Request, $psr7Response, Lambda::leaf()]);

				break;
			}
		}

		return $psr7Response;
	}

	/**
	 * @param array $routes
	 * @return array
	 */
	protected function compile(array $routes)
	{
		$compiled = [];

		foreach ($routes as $pattern => $middleware) {
			$regex = sprintf('#%s#', $pattern);

			// Build route
			$route = [
				'pattern' => $pattern,
				'regex' => $regex,
				'middleware' => $middleware,
				'variables' => [],
			];

			// Match and compile regex variables
			$variables = Regex::matchAll($pattern, '#{([a-zA-Z0-9]+)(?:\:(.*))?}#U');
			if ($variables) {
				$used = [];
				foreach ($variables[0] as $n => $variable) {
					$varName = $variables[1][$n];
					$varRe = $variables[2][$n] ?: '.+';

					// Validate multiuse of 1 variable
					if (in_array($varName, $used)) throw new InvalidStateException(sprintf('Variable %s is already used', $varName));
					$used[] = $varName;

					// Update regex
					$regex = str_replace($variable, sprintf('(?P<%s>%s)', $varName, $varRe), $regex);
				}

				$route['variables'] = $used;
				$route['regex'] = $regex;
			}

			// Append to compiled
			$compiled[] = $route;
		}

		return $compiled;
	}

}
