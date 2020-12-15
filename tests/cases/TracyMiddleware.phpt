<?php declare(strict_types = 1);

/**
 * Test: TracyMiddleware
 */

use Contributte\Middlewares\TracyMiddleware;
use Contributte\Psr7\Psr7ResponseFactory;
use Contributte\Psr7\Psr7ServerRequestFactory;
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
test(function (): void {
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
test(function (): void {
	$middleware = TracyMiddleware::factory(true);
	$middleware->setMode(Debugger::PRODUCTION);
	$middleware->setLogDir(TEMP_DIR);
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
	Assert::match('#((PHP Warning: Undefined variable \$a in)|(PHP Notice: Undefined variable: a in ))#', file_get_contents(TEMP_DIR . '/error.log'));
	Assert::count(1, MemoryMailer::$mails);
});
