<?php declare(strict_types = 1);

use Contributte\Middlewares\TracyMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tester\Assert;
use Tests\Fixtures\MemoryMailer;
use Tracy\Debugger;
use Tracy\Logger;

require_once __DIR__ . '/../bootstrap.php';

/** @var Logger $logger */
$logger = Debugger::getLogger();
$logger->mailer = [MemoryMailer::class, 'mail'];

// Disabled
Toolkit::test(function (): void {
	Assert::exception(function (): void {
		$middleware = TracyMiddleware::factory(true);
		$middleware->disable();
		$middleware(
			Psr7ServerRequestFactory::fromSuperGlobal(),
			Psr7ResponseFactory::fromGlobal(),
			function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): void {
				throw new RuntimeException('Foobar');
			}
		);
	}, RuntimeException::class, 'Foobar');
});

// Warnings
Toolkit::test(function (): void {
	$middleware = TracyMiddleware::factory(true);
	$middleware->setMode(Debugger::Production);
	$middleware->setLogDir(Environment::getTestDir());
	$middleware->setEmail('dev@contributte.org');
	$middleware(
		Psr7ServerRequestFactory::fromSuperGlobal(),
		Psr7ResponseFactory::fromGlobal(),
		function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): ResponseInterface {
			// phpcs:ignore
			$a++;

			return $psr7Response;
		}
	);
	// Support multiple php and package versions
	Assert::match('#((PHP Warning: Undefined variable \$a in)|(PHP Notice: Undefined variable: a in ))#', file_get_contents(Environment::getTestDir() . '/error.log'));
	Assert::count(1, MemoryMailer::$mails);
});
