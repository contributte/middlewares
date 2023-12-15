<?php declare(strict_types = 1);

use Contributte\Middlewares\Application\NetteMiddlewareApplication;
use Contributte\Psr7\Psr7Response;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\Notes;
use Nette\Application\Responses\TextResponse;
use Nette\Http\RequestFactory;
use Nette\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test invoking of callback
Toolkit::test(function (): void {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
		Notes::add('touched');

		return $response;
	};

	$app = new NetteMiddlewareApplication($callback);
	$app->run();

	Assert::equal(['touched'], Notes::fetch());
});

// Response text
Toolkit::test(function (): void {
	$callback = function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
		$response->getBody()->write('OK');

		return $response;
	};

	$app = new NetteMiddlewareApplication($callback);
	ob_start();
	$app->run();
	Assert::equal('OK', ob_get_contents());
});

// Response text with Nette
Toolkit::test(function (): void {
	$callback = function (ServerRequestInterface $request, Psr7Response $response): ResponseInterface {
		$response = $response->withApplicationResponse(new TextResponse('NETTE'));
		$response->getBody()->write('OK');

		return $response;
	};

	$app = new NetteMiddlewareApplication($callback);
	$app->setHttpRequest((new RequestFactory())->createHttpRequest());
	$app->setHttpResponse(new Response());
	ob_start();
	$app->run();
	Assert::equal('NETTE', ob_get_contents());
});

// Throws exception
Toolkit::test(function (): void {
	Assert::throws(function (): void {
		$callback = fn (ServerRequestInterface $request, ResponseInterface $response) => null;

		$app = new NetteMiddlewareApplication($callback);
		$app->run();
	}, RuntimeException::class);
});
