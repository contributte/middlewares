<?php declare(strict_types = 1);

namespace Tests;

use Contributte\Middlewares\PresenterMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Exception;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\Routers\RouteList;
use Ninjify\Nunjuck\Notes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;
use Tests\Fixtures\PresenterFactory;

require_once __DIR__ . '/../bootstrap.php';

test(function (): void {
	$presenterFactory = new PresenterFactory([
		'Tests/Fixtures/Exception' => function () {
			Notes::add('CALLED');
			throw new Exception('simulated exception');
		},
		'Test/Fixtures/ErrorHandling' => function () {
			Notes::add('ERROR_HANDLED');
			return new JsonResponse(['status' => 500]);
		},
	]);

	$router = new RouteList();
	$router->addRoute('/', 'Tests/Fixtures/Exception:run');

	$_SERVER['REQUEST_METHOD'] = 'FORWARD';
	$request = Psr7ServerRequestFactory::fromGlobal();

	$middleware = new PresenterMiddleware($presenterFactory, $router);
	$middleware->setErrorPresenter('Test/Fixtures/ErrorHandling');

	Assert::noError(
		function () use ($middleware, $request) {
			$middleware(
				$request,
				Psr7ResponseFactory::fromGlobal(),
				function (ServerRequestInterface $req, ResponseInterface $res): ResponseInterface {
					return $res;
				}
			);
		}
	);

	Assert::equal(['CALLED', 'ERROR_HANDLED'], Notes::fetch());
});
