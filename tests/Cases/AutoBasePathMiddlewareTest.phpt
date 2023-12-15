<?php declare(strict_types = 1);

namespace Tests\Cases;

use Contributte\Middlewares\AutoBasePathMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

final class AutoBasePathMiddlewareTest extends TestCase
{

	/**
	 * @dataProvider pathsData
	 */
	public function testPaths(string $requestUri, string $scriptName, string $basePath, string $coolUrl): void
	{
		$middleware = new AutoBasePathMiddleware();
		$_SERVER['REQUEST_URI'] = $requestUri; // @phpcs:ignore
		$_SERVER['SCRIPT_NAME'] = $scriptName; // @phpcs:ignore
		$middleware(
			Psr7ServerRequestFactory::fromSuperGlobal(),
			Psr7ResponseFactory::fromGlobal(),
			function (ServerRequestInterface $req, ResponseInterface $res) use ($requestUri, $basePath, $coolUrl): ResponseInterface {
				Assert::equal($requestUri, $req->getAttribute(AutoBasePathMiddleware::ATTR_ORIGINAL_PATH));
				Assert::equal($basePath, $req->getAttribute(AutoBasePathMiddleware::ATTR_BASE_PATH));
				Assert::equal($coolUrl, $req->getAttribute(AutoBasePathMiddleware::ATTR_PATH));
				Assert::equal($coolUrl, $req->getUri()->getPath());

				return $res;
			}
		);
	}

	/**
	 * @return string[][]
	 */
	public function pathsData(): array
	{
		return [
			['/foo/bar/cool-url', '/foo/bar/index.php', '/foo/bar/', '/cool-url'],
			['/foo/bar/cool-url/', '/foo/bar/index.php', '/foo/bar/', '/cool-url/'],
			['/foo/bar/cool-url/baz', '/foo/bar/index.php', '/foo/bar/', '/cool-url/baz'],
			['/foo/bar/cool-url//baz', '/foo/bar/index.php', '/foo/bar/', '/cool-url//baz'],
			['/foo/bar/cool-url//', '/foo/bar/index.php', '/foo/bar/', '/cool-url//'],
			['/foo/bar/123456', '/foo/bar/index.php', '/foo/bar/', '/123456'],
			['/foo/bar/invalid', '/bar/bar/index.php', '/', '/foo/bar/invalid'],
			['/', '/', '/', '/'],
			['', '', '', '/'],
		];
	}

}

(new AutoBasePathMiddlewareTest())->run();
