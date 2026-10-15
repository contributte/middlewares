<?php declare(strict_types = 1);

use Contributte\Middlewares\BasicAuthMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\Notes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

function createRequest(?string $authorization = null): ServerRequestInterface
{
	$request = Psr7ServerRequestFactory::fromGlobal();

	return $authorization === null ? $request : $request->withHeader('Authorization', $authorization);
}

function basic(string $username, string $password): string
{
	return 'Basic ' . base64_encode($username . ':' . $password);
}

function process(BasicAuthMiddleware $middleware, ServerRequestInterface $request): ResponseInterface
{
	return $middleware($request, Psr7ResponseFactory::fromGlobal(), function (ServerRequestInterface $req, ResponseInterface $res): ResponseInterface {
		Notes::add('CALLED');
		Notes::add($req->getAttribute('username'));

		return $res;
	});
}

// Missing header is rejected with default realm
Toolkit::test(function (): void {
	$middleware = (new BasicAuthMiddleware())->addUser('foo', 'bar', true);

	$response = process($middleware, createRequest());

	Assert::equal([], Notes::fetch());
	Assert::equal(401, $response->getStatusCode());
	Assert::equal('Basic realm="Restrict zone"', $response->getHeaderLine('WWW-Authenticate'));
});

// Custom realm title
Toolkit::test(function (): void {
	$middleware = new BasicAuthMiddleware('Admin');

	$response = process($middleware, createRequest());

	Assert::equal('Basic realm="Admin"', $response->getHeaderLine('WWW-Authenticate'));
});

// Unsecured (plain-text) password
Toolkit::test(function (): void {
	$middleware = (new BasicAuthMiddleware())->addUser('foo', 'bar', true);

	$response = process($middleware, createRequest(basic('foo', 'bar')));

	Assert::equal(['CALLED', 'foo'], Notes::fetch());
	Assert::equal(200, $response->getStatusCode());
});

// Hashed password
Toolkit::test(function (): void {
	$middleware = (new BasicAuthMiddleware())->addUser('foo', password_hash('bar', PASSWORD_DEFAULT));

	process($middleware, createRequest(basic('foo', 'bar')));

	Assert::equal(['CALLED', 'foo'], Notes::fetch());
});

// Password containing colon
Toolkit::test(function (): void {
	$middleware = (new BasicAuthMiddleware())->addUser('foo', 'b:a:r', true);

	process($middleware, createRequest(basic('foo', 'b:a:r')));

	Assert::equal(['CALLED', 'foo'], Notes::fetch());
});

// Rejected credentials and malformed headers
Toolkit::test(function (): void {
	$middleware = (new BasicAuthMiddleware())
		->addUser('foo', 'bar', true)
		->addUser('baz', password_hash('qux', PASSWORD_DEFAULT));

	$headers = [
		'wrong plain password' => basic('foo', 'wrong'),
		'wrong hashed password' => basic('baz', 'wrong'),
		'hash used as password' => basic('baz', (string) password_hash('qux', PASSWORD_DEFAULT)),
		'unknown user' => basic('unknown', 'bar'),
		'other scheme' => 'Bearer ' . base64_encode('foo:bar'),
		'missing colon' => 'Basic ' . base64_encode('foobar'),
		'invalid base64' => 'Basic !!!',
		'empty credentials' => 'Basic ',
	];

	foreach ($headers as $case => $header) {
		$response = process($middleware, createRequest($header));

		Assert::equal([], Notes::fetch(), $case);
		Assert::equal(401, $response->getStatusCode(), $case);
	}
});
