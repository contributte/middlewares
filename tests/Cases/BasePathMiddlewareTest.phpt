<?php declare(strict_types = 1);

namespace Tests\Cases;

use Contributte\Middlewares\BasePathMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

final class BasePathMiddlewareTest extends TestCase
{

	/**
	 * @dataProvider providePaths
	 */
	public function testPaths(string $requestUri, string $basePath, string $coolUrl): void
	{
		$middleware = new BasePathMiddleware($basePath);
		$_SERVER['REQUEST_URI'] = $requestUri; // @phpcs:ignore
		$middleware(
			Psr7ServerRequestFactory::fromSuperGlobal(),
			Psr7ResponseFactory::fromGlobal(),
			function (ServerRequestInterface $req, ResponseInterface $res) use ($coolUrl): ResponseInterface {
				Assert::equal($coolUrl, $req->getUri()->getPath());

				return $res;
			}
		);
	}

	/**
	 * @return string[][]
	 */
	public function providePaths(): array
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
