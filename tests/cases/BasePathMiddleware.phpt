<?php

/**
 * Test: AutoBasePathMiddleware
 */

namespace Tests;

use Contributte\Middlewares\BasePathMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class BasePathMiddlewareTest extends TestCase
{

	/**
	 * @dataProvider pathsData
	 *
	 * @param string $requestUri
	 * @param string $basePath
	 * @param string $coolUrl
	 * @return void
	 */
	public function testPaths($requestUri, $basePath, $coolUrl)
	{
		$middleware = new BasePathMiddleware($basePath);
		$_SERVER['REQUEST_URI'] = $requestUri;
		$middleware(
			Psr7ServerRequestFactory::fromSuperGlobal(),
			Psr7ResponseFactory::fromGlobal(),
			function (ServerRequestInterface $req, ResponseInterface $res) use ($requestUri, $basePath, $coolUrl) {
				Assert::equal($coolUrl, $req->getUri()->getPath());
			}
		);
	}

	/**
	 * @return array
	 */
	public function pathsData()
	{
		return [
			['/foo/bar/cool-url', '/foo/bar/', '/cool-url'],
			['/foo/bar/cool-url/', '/foo/bar/', '/cool-url/'],
			['/foo/bar/cool-url/baz', '/foo/bar/', '/cool-url/baz'],
			['/foo/bar/cool-url//baz', '/foo/bar/', '/cool-url//baz'],
			['/foo/bar/cool-url//', '/foo/bar/', '/cool-url//'],
			['/foo/bar/123456', '/foo/bar/', '/123456'],
			['/foo/bar/invalid', '/', '/foo/bar/invalid'],
			['/foo/bar/invalid', '', '/foo/bar/invalid'],
		];
	}

}

(new BasePathMiddlewareTest())->run();
