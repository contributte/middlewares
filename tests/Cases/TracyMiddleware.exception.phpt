<?php declare(strict_types = 1);

/**
 * @exitCode 255
 * @httpCode 500
 */

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

Toolkit::test(function (): void {
	$middleware = TracyMiddleware::factory(true);
	$middleware->setMode(Debugger::Development);
	$middleware->setEmail('dev@contributte.org');
	$middleware->setLogDir(Environment::getTestDir());

	register_shutdown_function(function (): void {
		Assert::match('%a%Error: Call to undefined function missing_function() in %a%', file_get_contents(Debugger::$logDirectory . '/exception.log'));
		Assert::true(is_file(Debugger::$logDirectory . '/email-sent'));
		Assert::count(1, MemoryMailer::$mails);
	});

	$middleware(Psr7ServerRequestFactory::fromSuperGlobal(), Psr7ResponseFactory::fromGlobal(), function (ServerRequestInterface $psr7Request, ResponseInterface $psr7Response): void {
		missing_function();
	});
});
